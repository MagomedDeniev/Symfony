<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Form\ActivityType;
use App\Repository\ActivityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Security('is_granted("ROLE_OWNER")')]
class ActivityController extends AbstractController
{
    #[Route('/activities', name: 'activities_index', methods: ['GET'])]
    public function index(ActivityRepository $activityRepo): Response
    {
        return $this->render('interface/activity/index.html.twig', [
            'activities' => $activityRepo->findAll(),
        ]);
    }

    #[Route('/activity/new', name: 'activity_new', methods: ['GET','POST'])]
    public function new(Request $request, ActivityRepository $activityRepo): Response
    {
        $activity = new Activity();
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activityRepo->add($activity);
            return $this->redirectToRoute('activities_index');
        }

        return $this->render('interface/activity/new.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/activity/{id}/edit', name: 'activity_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Activity $activity, ActivityRepository $activityRepo): Response
    {
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $activityRepo->flush();
            return $this->redirectToRoute('activities_index');
        }

        return $this->render('interface/activity/edit.html.twig', [
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/activity/{id}', name: 'activity_delete', methods: ['POST'])]
    public function delete(Request $request, Activity $activity, ActivityRepository $activityRepo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$activity->getId(), $request->request->get('_token'))) {
            $activityRepo->remove($activity);
        }

        return $this->redirectToRoute('activities_index');
    }
}
