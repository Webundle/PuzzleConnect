<?php

namespace Puzzle\ConnectBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Puzzle\ConnectBundle\Entity\User;
use Puzzle\ConnectBundle\Event\UserEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Puzzle\ConnectBundle\UserEvents;

/**
 * Create a new admin
 *
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class CreateAdminCommand extends Command
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;
    
    /**
     * @var EventDispatcherInterface $dispatcher
     */
    private $dispatcher;
    
    /**
     * @var string $firstName
     */
    private $firstName;
    
    /**
     * @var string $lastName
     */
    private $lastName;
    
    /**
     * @var string $email
     */
    private $email;
    
    /**
     * @var string $username
     */
    private $username;
    
    /**
     * @var string $plainPassword
     */
    private $plainPassword;
    
    
    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Console\Command\Command::configure()
	 */
	protected function configure()
	{
		$this
    		->setName('puzzle:user:create-admin')
    		->setDescription('Creates a new admin')
    		->setHelp(
				<<<EOT
                    The <info>%command.name%</info>command creates a new admin.
	 <info>php %command.full_name% [--redirect-uri=...] [--grant-type=...] name</info>
EOT
		);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Symfony\Component\Console\Command\Command::interact()
	 */
	protected function interact(InputInterface $input, OutputInterface $output)
	{
	    $dialog = $this->getHelper('question');
		
		while(!$this->firstName){
		    $this->firstName = $dialog->ask($input, $output, new Question('First name: '));
		}
		
		$this->lastName = $dialog->ask($input, $output, new Question('Last name: '));
		
		while(!$this->email){
		    $this->email = $dialog->ask($input, $output, new Question('E-mail: '));
		}
		
		while(!$this->username){
		    $question = new Question('Username: ');
		    $question->setAutocompleterValues([$this->email]);
		    $this->username = $dialog->ask($input, $output, $question);
		}
		
		while(!$this->plainPassword){
		    $this->plainPassword = $dialog->ask($input, $output, new Question('Mot de passe: '));
		}
	}

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Admin Credentials');

        $user = new User();
        $user->setFirstName($this->firstName);
        $user->setLastName($this->lastName);
        $user->setEmail($this->email);
        $user->setUsername($this->username);
        $user->setPlainPassword($this->plainPassword);
        $user->setRoles([User::ROLE_ADMIN]);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        /** @var EventDispatcher */
        $this->dispatcher->dispatch(UserEvents::USER_PASSWORD, new UserEvent($user, [
            'plainPassword' => $user->getPlainPassword()
        ]));
        
        // Give the credentials back to the user
        $headers = ['Username', 'Password'];
        $rows = [
            [$user->getUsername(), $user->getPlainPassword()],
        ];

        $io->table($headers, $rows);

        return 0;
    }
}
