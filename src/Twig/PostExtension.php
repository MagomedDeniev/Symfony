<?php

namespace App\Twig;

use App\Entity\Post;
use App\Form\NotificationType;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PostExtension extends AbstractExtension
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('postValidation', [$this, 'postValidation'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('postInfo', [$this, 'postInfo'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('postTitle', [$this, 'postTitle'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('postImage', [$this, 'postImage'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('postDescription', [$this, 'postDescription'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('postTaggedUsers', [$this, 'postTaggedUsers'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('postActions', [$this, 'postActions'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('postView', [$this, 'postView'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    private function createForm(string $type, $data = null, array $options = []): FormInterface
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function postValidation(Environment $twig, Post $post): string
    {
        $form = $this->createForm(NotificationType::class, null,['gender' => $post->getGender()]);

        return $twig->render('interface/post/layouts/post_validation.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function postInfo(Environment $twig, $post): string
    {
        return $twig->render('interface/post/layouts/post_info.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function postTitle(Environment $twig, $post): string
    {
        return $twig->render('interface/post/layouts/post_title.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function postImage(Environment $twig, $post, $view = 'feed'): string
    {
        return $twig->render('interface/post/layouts/post_image.html.twig', [
            'post' => $post,
            'view' => $view
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function postDescription(Environment $twig, $post): string
    {
        return $twig->render('interface/post/layouts/post_description.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function postTaggedUsers(Environment $twig, $post): string
    {
        return $twig->render('interface/post/layouts/post_tagged_users.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function postActions(Environment $twig, $post, $comments = true): string
    {
        return $twig->render('interface/post/layouts/post_actions.html.twig', [
            'post' => $post,
            'comments' => $comments
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function postView(Environment $twig, Post $post, $view = 'feed'): string
    {
        return $twig->render('interface/post/layouts/post_view.html.twig',[
            'post' => $post,
            'view' => $view
        ]);
    }
}
