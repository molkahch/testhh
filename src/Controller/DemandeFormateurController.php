<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\DemandeFormateurRepository;
use App\Entity\DemandeFormateur;
use App\Entity\CV;
use Doctrine\ORM\Query\Parameter;
use App\Form\DemandeFType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Smalot\PdfParser\Parser;


class DemandeFormateurController extends AbstractController
{
    
    /**
     * @Route("/demande/formateur/new", name="Adddemandeformateur")
     * Method({"GET","POST"})
     */    
    public function Add(Request $request): Response
    {
        $demandeformateur = new DemandeFormateur();
        $form =$this->createForm(DemandeFType::class,$demandeformateur);
        $form->handleRequest($request);
        $this->addFlash('success','Demande envoyé avec succés!');

        if($form->isSubmitted() && $form->isValid())
        {
           
            $file = $form->get('CV')->getData();
            $filename = md5(uniqid()).'.'.$file->guessExtension();
            $file->move($this->getParameter('uploads_directory'),$filename);
            $demandeformateur->setCv($filename);

            
            $entityManager=$this->getDoctrine()->getManager();
            $entityManager->persist($demandeformateur);
            $entityManager->flush();
            $response=$this->forward('App\Controller\CVController::ajoutcv',['CV'=>$filename,'demandeformateur'=>$demandeformateur]);
           //usercontroller:$response=$this->forward('App\Controller\CVController::ajoutcv',['CV'=>$filename,'user'=>$user]);

           return $this->redirectToRoute('list_demande_formateur');     
           
            //$serializer = new Serializer([new ObjectNormalizer()]);
            //$formatted= $serializer->normalize($demandeformateur);
           // return new JsonResponse($formatted);

        }
        
        return $this->render('demandeFormateur/addDemandeFormateur.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/demande/formateur/list", name="list_demande_formateur")
     */
    public function list(): Response
    {
        $form=$this->getDoctrine()->getRepository(DemandeFormateur::class)
        ->findAll();
        //$serializer = new Serializer([new ObjectNormalizer()]);
        //$formatted= $serializer->normalize($form);
        //return new JsonResponse($formatted);
        return $this->render('demandeformateur/listDemandeFormateur.html.twig', [
            'form' => $form,
        ]);
    }


    
    /**
     * @Route("/demande/formateur/Delete/{id}" , name="Delete_formateur")
     *Method({"DELETE"})
     */

    public function Delete(Request $request,$id)
    {
            $demandeformateur = $this->getDoctrine()
            ->getRepository(DemandeFormateur::class)
            ->find($id);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($demandeformateur);
            $entityManager->flush();

           // $serializer = new Serializer([new ObjectNormalizer()]);
            //$formatted= $serializer->normalize("Demande supprimée!");
            //return new JsonResponse($formatted);
            return $this->redirectToRoute('list_demande_formateur');     
    }

    /**
    * @Route("/demnade/formateur/download/{id}", name="download_file")
    **/
    public function downloadFileAction($id)
    {
            
            $demandeformateur = $this->getDoctrine()
            ->getRepository(DemandeFormateur::class)
            ->find($id);
            $var='uploads/'. $demandeformateur->getCV();
            (string)$var;
            $response = new BinaryFileResponse($var);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
            return $response;
    }

    /**
    * @return Response
    * @Route ("/home" , name="home")
    */
    public function home()
    {
        return $this->render('home.html.twig');
        }
    
   
}
