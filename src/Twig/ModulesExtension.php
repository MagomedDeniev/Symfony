<?php

namespace App\Twig;

use App\Entity\Comment;
use App\Entity\Message;
use App\Entity\User;
use App\Form\CommentType;
use App\Repository\MessageRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Service\Defender;
use App\Service\Paginator;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModulesExtension extends AbstractExtension
{
    private $tags;
    private $paginator;
    private $container;
    private $messageRepo;
    private $security;
    private $defender;
    private $users;

    public function __construct(Security $security, Defender $defender, TagRepository $tags, Paginator $paginator, ContainerInterface $container, MessageRepository $messageRepo, UserRepository $userRepo)
    {
        $this->users = $userRepo;
        $this->security = $security;
        $this->defender = $defender;
        $this->paginator = $paginator;
        $this->container = $container;
        $this->tags = $tags;
        $this->messageRepo = $messageRepo;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('tags', [$this, 'tags'], ['is_safe' => ['html']]),
            new TwigFunction('comments', [$this, 'comments'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('breadcrumb', [$this, 'breadcrumb'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('oneComment', [$this, 'oneComment'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('lastMessage', [$this, 'lastMessage'], ['is_safe' => ['html']]),
        ];
    }

    public function tags($type = null)
    {
        return $this->tags->findByType($type);
    }

    public function createForm(string $type, $data = null, array $options = []): FormInterface
    {
        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function comments(Environment $twig, $entity, $page, $params): string
    {
        $name = strtolower((new \ReflectionClass($entity))->getShortName());

        $this->paginator->setClass(Comment::class)
            ->setMethod('getNoParentComments')
            ->setType('comments')
            ->setOrder(['id' => 'DESC'])
            ->setCriteria([$name => $entity])
            ->setParameters($params)
            ->setLimit(30)
            ->setPage($page);

        return $twig->render('interface/layouts/comments/comment_block.html.twig', [
            'commentForm' => $this->createForm(CommentType::class)->createView(),
            'comments' => $this->paginator->getData(),
            'paginator' => $this->paginator,
            'entity' => $entity,
            'name' => $name
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function breadcrumb(Environment $twig, $links, $entity = null, $name = null): string
    {
        return $twig->render('interface/layouts/service/breadcrumb.html.twig', [
            'links' => $links,
            'entity' => $entity,
            'name' => $name
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function oneComment(Environment $twig, Comment $comment): string
    {
        return $twig->render('interface/layouts/comments/one_comment.html.twig', [
            'comment' => $comment
        ]);
    }

    public function lastMessage(User $user)
    {
        $message = $this->messageRepo->findConversation(['user_one' => $this->getUser(),'user_two' => $user], ['sentAt' => 'DESC'],1);

        return $message[0];
    }

    private function getUser()
    {
        if ($this->defender->isGranted($this->security->getUser(),'ROLE_GUEST')) {
            return $this->security->getUser();
        } else {
            return $this->users->findOneBy(['username' => $this->security->getUser()->getUsername()]);
        }
    }
}
