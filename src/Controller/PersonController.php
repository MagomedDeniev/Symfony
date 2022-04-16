<?php

namespace App\Controller;

use App\CustomAbstracts\CustomAbstractController;
use App\Entity\Person;
use App\Form\PersonType;
use App\Repository\PeopleRepository;
use App\Repository\SongRepository;
use App\Service\Initializer;
use App\Twig\SongExtension;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonController extends CustomAbstractController
{
    #[Route('/people/{letter}', name: 'people_by_letter',  methods: ['GET'])]
    public function people($letter, PeopleRepository $personRepo, SongExtension $extension): Response
    {
        if (!key_exists($letter,$extension->letters())) {
            return $this->redirectToRoute('song_index');
        }

        if ($this->isGranted('ROLE_PEOPLE_MODERATOR')) {
            $people = $personRepo->findPeopleByLetter($extension->letters()[$letter]);
        } else {
            $people = $personRepo->findPeopleByLetter($extension->letters()[$letter],true);
        }

        return $this->render('interface/person/people.html.twig', [
            'people' => $people,
            'letter' => $extension->letters()[$letter]
        ]);
    }

    #[Route('/person/new', name: 'person_new',  methods: ['GET','POST'])]
    #[Security('is_granted("ROLE_PEOPLE_MODERATOR")')]
    public function new(Request $request, Initializer $initializer): Response
    {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $initializer->initializePersonNew($person);

            return $this->redirectToRoute('person_show', [
                'slug' => $person->getSlug()
            ]);
        }

        return $this->render('interface/person/new.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/person/{slug}', name: 'person_show',  methods: ['GET'])]
    public function show($slug, SongRepository $songRepo, PeopleRepository $people, PeopleRepository $peopleRepo): Response
    {
        if (!$this->isGranted("ROLE_PEOPLE_MODERATOR")) {
            try {
                $person = $people->findActiveSongsPerson($slug);
            }
            catch (NoResultException $e) {
                return $this->redirectToRoute('song_index');
            }
        } else {
            $person = $peopleRepo->findOneBy(['slug' => $slug]);
        }

        $songs = $songRepo->findBy(['vocalist' => $person, 'status' => true], ['releaseDate' => 'DESC']);

        return $this->render('interface/person/person.html.twig', [
            'person' => $person,
            'songs' => $songs
        ]);
    }

    #[Route('/person/{slug}/edit', name: 'person_edit',  methods: ['GET','POST'])]
    #[Security('is_granted("ROLE_PEOPLE_MODERATOR")')]
    public function edit(Request $request, Person $person, Initializer $initializer): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $initializer->initializePersonEdit($person);

            return $this->redirectToRoute('person_show', ['slug' => $person->getSlug()]);
        }

        return $this->render('interface/person/edit.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'person_delete',  methods: ['POST'])]
    #[Security('is_granted("ROLE_OWNER")')]
    public function delete(Request $request, Person $person, PeopleRepository $peopleRepo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$person->getId(), $request->request->get('_token'))) {
            $peopleRepo->remove($person);
        }

        return $this->redirectToRoute('app_home');
    }
}
