<?php

namespace App\Controller;

use App\Entity\PlaygroundSubmit;
use App\Form\PlaygroundSubmitType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;
use Symfony\UX\Turbo\TurboStreamResponse;

class PlaygroundController extends AbstractController
{
    #[Route('/playground', name: 'app_playground')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $submission = new PlaygroundSubmit();
        $form = $this->createForm(PlaygroundSubmitType::class, $submission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($submission);
            $entityManager->flush();

            $this->addFlash('success', 'Thanks! Your submission has been saved.');

            return $this->redirectToRoute('app_playground');
        }

        return $this->render('playground/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/playground/{id}/edit', name: 'app_playground_edit', requirements: ['id' => '\d+'])]
    public function edit(PlaygroundSubmit $submission, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlaygroundSubmitType::class, $submission, [
            'submit_label' => 'Save Changes',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                $rowHtml = $this->renderView('playground/_row.html.twig', [
                    'submission' => $submission,
                ]);

                return (new TurboStreamResponse())->replace('#submission_row_'.$submission->getId(), $rowHtml);
            }

            $this->addFlash('success', 'Submission updated!');

            return $this->redirectToRoute('app_playground');
        }

        return $this->render('playground/edit.html.twig', [
            'form' => $form,
            'submission' => $submission,
        ]);
    }
}
