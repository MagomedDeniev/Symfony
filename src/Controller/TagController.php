<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    #[Route('/tags', name: 'tags_index',  methods: ['GET'])]
    #[Security('is_granted("ROLE_OWNER")')]
    public function index(TagRepository $tagRepository): Response
    {
        return $this->render('interface/tag/index.html.twig', [
            'tags' => $tagRepository->findBy([],['title' => 'ASC']),
        ]);
    }

    #[Route('/tag/new', name: 'tag_new',  methods: ['GET','POST'])]
    #[Security('is_granted("ROLE_OWNER")')]
    public function new(Request $request, TagRepository $tagRepo): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tag->setUpdatedAt(new \DateTime('now'));
            $tagRepo->add($tag);
            return $this->redirectToRoute('tags_index');
        }

        return $this->render('interface/tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tag/{id}/edit', name: 'tag_edit',  methods: ['GET','POST'])]
    #[Security('is_granted("ROLE_OWNER")')]
    public function edit(Request $request, Tag $tag, TagRepository $tagRepo): Response
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tagRepo->flush();
            return $this->redirectToRoute('tags_index');
        }

        return $this->render('interface/tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/tag/{id}', name: 'tag_delete',  methods: ['POST'])]
    #[Security('is_granted("ROLE_OWNER")')]
    public function delete(Request $request, Tag $tag, TagRepository $tagRepo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            $tagRepo->remove($tag);
        }

        return $this->redirectToRoute('tags_index');
    }
}
