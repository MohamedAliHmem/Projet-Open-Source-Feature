<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\ResetPassword;
use App\Classe\Mail;
use DateTimeImmutable;
use App\Form\ResetPasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class ResetPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'reset_password')]
    public function index(Request $request)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_borrowing_index');
        }
        if ($request->get('email')) {
            // dd($request->get('email'));
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));
            //dd($user);
            if ($user) {
                //1- Save in the database the request reset of password
                $resetPassword = new ResetPassword();
                $resetPassword->setUser($user)
                ->setToken(uniqid())
                ->setCreatedAt(new DateTimeImmutable());
                $this->entityManager->persist($resetPassword);
                $this->entityManager->flush();
                //2- Send an email to the user with a link to update the password
                $url= $this->generateUrl('update_password',[
                'token'=>$resetPassword->getToken()
                ]);
                $content ="Hello ".$user->getEmail()."<br> In order to reset
                your password, please click on the following link :<br> ";
                $content .="<a href='http://127.0.0.1:8000".$url."'>Reset your password</a>.";
                $mail= new Mail();
                $mail->send($user->getEmail(),null ,'Reset your password',
                $content);
                $this->addFlash("notice", "An email has been sent to you !");
            }
            else{
                $this->addFlash("danger", " 🚫 this email does not exist !");
            }

            
            

        }
    return $this->render('reset_password/index.html.twig');
    }

    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/update-password/{token}', name: 'update_password')]
    public function reset($token  , Request $request , UserPasswordHasherInterface $encoder)
    {
       //dd($token);
       $resetPassword = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);
        if (!$resetPassword) 
        {
            return $this->redirectToRoute('reset_password');
        }
        else {
            $form = $this->createForm(ResetPasswordType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $resetPassword->getUser()->setPassword(
                    $encoder->hashPassword(
                    $resetPassword->getUser(),
                    $form->get('new_password')->getData()
                    )
                    );
                    $this->entityManager->flush();
                    $this->addFlash('notice', 'Your password has been updated !');
                    return $this->redirectToRoute('login');
                  
                }
            
            $now = new \DateTime();
            if ($resetPassword->getCreatedAt()->modify('+ 30 minute') < ($now)) {
                $this->addFlash("notice", "⌛ Password reset token expired !");
                die("Your password request has expired. Please renew it");
            }
            return $this->render('reset_password/reset.html.twig', [
                'form' => $form->createView() ]); 
           

            }
            
            dump($resetPassword->getCreatedAt()->modify('+ 30 minute'));
            dd($resetPassword);

           
    }

    

    
}
