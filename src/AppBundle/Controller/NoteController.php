<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Note;
use AppBundle\Form\NoteType;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Util\Codes;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class NoteController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  resource = true,
     *  description = "List all notes",
     *  statusCodes = {
     *     200 = "Returned when successful",
     *     500 = "Error"
     *   }
     * )
     * @return \Symfony\Component\HttpFoundation\\Symfony\Component\HttpFoundation\Response
     */
    public function getNotesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $notes = $em->getRepository('AppBundle:Note')->findAll();

        $view = $this->view($notes, 200);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *  resource = true,
     *  description = "Get a note",
     *  statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the deal is not found"
     *   }
     * )
     * @ParamConverter("note", class="AppBundle:Note")
     * @param Note $note
     * @return \Symfony\Component\HttpFoundation\\Symfony\Component\HttpFoundation\Response
     */
    public function getNoteAction(Note $note)
    {
        $view = $this->view($note, 200);

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  input="AppBundle\Form\NoteType",
     *  description="Creates a new note",
     *  statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\\Symfony\Component\HttpFoundation\Response
     */
    public function postNotesAction(Request $request)
    {
        $note = new Note();
        $form = $this->createForm(new NoteType(), $note);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($note);
            $em->flush();

            $view = $this->routeRedirectView('get_note', ['note' => $note->getId()]);
        } else {
            $view = $this->view(['form' => $form], 400);
        }

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Update partially existing note from the submitted data.",
     *  input="AppBundle\Form\NoteType",
     *  statusCodes = {
     *     204 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     * @ParamConverter("note", class="AppBundle:Note")
     * @param Note $note
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\\Symfony\Component\HttpFoundation\Response
     */
    public function patchNoteAction(Note $note, Request $request)
    {
        $form = $this->createForm(new NoteType(), $note, array('method' => 'PATCH'));
        $form->handleRequest($request);

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $view = $this->routeRedirectView('get_note', ['note' => $note->getId()], Codes::HTTP_NO_CONTENT);
        } else {
            $view = $this->view(['form' => $form], 400);
        }

        return $this->handleView($view);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Deletes a note",
     *  statusCodes={
     *     204="Returned when successful",
     *     404="Returned when the note is not found"
     *   }
     * )
     * @ParamConverter("note", class="AppBundle:Note")
     * @param Note $note
     * @return \Symfony\Component\HttpFoundation\\Symfony\Component\HttpFoundation\Response
     */
    public function deleteNoteAction(Note $note)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($note);
        $em->flush();

        return $this->handleView(
            $this->routeRedirectView('get_notes', [], Codes::HTTP_NO_CONTENT)
        );
    }
}
