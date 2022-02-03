<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Console\Exception\RuntimeException;
use App\Entity\User;

class SetUserIsVerifiedCommand extends Command
{
    protected static $defaultName = 'app:set-user-isVerified';
    
    /**
     * @var SymfonyStyle
     */
    private $io;
    
    
    private $entityManager;
    private $user;
    
    public function __construct(EntityManagerInterface $em, UserRepository $user)
    {
        parent::__construct();
        
        $this->entityManager = $em;
        $this->user = $user;
    }
    
    protected function configure(): void
    {
        $this
        ->setDescription('Set User\'s IsVerified and update the database')
        ->addArgument('username', InputArgument::OPTIONAL, 'The username of the user')
        ->addArgument('isVerified', InputArgument::OPTIONAL, 'The isVerified of the user')
        ;
    }
    
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }
    
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getArgument('username') && null !== $input->getArgument('isVerified'))
        {
            return;
        }
        
        $this->io->title('Set User Is Verified Command Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:set-user-isVerified username isVerified',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);
        
        
        // Ask for the username if it's not defined
        $username = $input->getArgument('username');
        if (null !== $username) {
            $this->io->text(' > <info>Username</info>: '.$username);
        } else {
            $input->setArgument('username', $username);
        }
        
        // Ask for the isVerified if it's not defined
        $isVerified = $input->getArgument('isVerified');
        if (null !== $isVerified)
        {
            $this->io->text(' > <info>IsVerified</info>: '.$isVerified);
        } else {
            $input->setArgument('isVerified', $isVerified);
        }
    }
    
    private function getUser($userName): User
    {
        $user = $this->user->findOneBy(['username' => $userName]);
        
        if (null == $user) {
            throw new RuntimeException(sprintf('There is no user registered with the "%s" username.', $userName));
        }
        
        return $user;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopWatch = new Stopwatch();
        $stopWatch->start('set-user-isVerified-command');
        
        $userName = $input->getArgument('username');
        $isVerified = $input->getArgument('isVerified');
        
        $user = $this->getUser($userName);
        $user->setIsVerified($isVerified);
        
        $this->entityManager->flush();
        
        $this->io->success(sprintf('%s was successfully updated: %s (%s)',  $user->getUsername(), $user->getRoles()));
        
        $event = $stopWatch->stop('set-user-isVerified-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('User database id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $user->getId(), $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }
        
        return Command::SUCCESS;
        
    }
}

