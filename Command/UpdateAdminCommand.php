<?php

namespace Puzzle\ConnectBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Puzzle\ConnectBundle\Entity\User;
use Puzzle\ConnectBundle\Event\UserEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Puzzle\ConnectBundle\UserEvents;

/**
 * Update OAuth Client
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class UpdateAdminCommand extends Command
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
	
	/**
	 * @var User $user
	 */
    private $user;
	
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
		->setName('puzzle:user:update-admin')
		->setDescription('Update an admin account')
		->addArgument('username', InputArgument::REQUIRED, 'Get username')
		->setHelp(
				<<<EOT
                    The <info>%command.name%</info>command update an admin.
	
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
	    $username = $input->getArgument("username");
	    $this->user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
		
	    $question = new Question('First name: ');
	    $question->setAutocompleterValues([$this->user->getFirstName()]);
	    $this->firstName = $dialog->ask($input, $output, $question);
	    
		$question = new Question('Last name: ');
		$question->setAutocompleterValues([$this->user->getLastName()]);
		$this->lastName = $dialog->ask($input, $output, $question);
		
		$question = new Question('E-mail: ');
		$question->setAutocompleterValues([$this->user->getEmail()]);
		$this->email = $dialog->ask($input, $output, $question);
		
		$question = new Question('Username: ');
		$question->setAutocompleterValues([$this->user->getUsername()]);
		$this->username = $dialog->ask($input, $output, $question);
		
		$this->plainPassword = $dialog->ask($input, $output, new Question('Mot de passe: '));
	}

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Admin Credentials updated');
        
        if ($this->firstName){
            $this->user->setFirstName($this->firstName);
        }
        
        if ($this->lastName){
            $this->user->setLastName($this->lastName);
        }
        
        if ($this->email){
            $this->user->setEmail($this->email);
        }
        
        if ($this->username){
            $this->user->setUsername($this->username);
        }
        
        if ($this->plainPassword){
            $user->setPlainPassword($this->plainPassword);
            $user->setRoles([User::ROLE_ADMIN]);
            
            /** @var EventDispatcher */
            $this->dispatcher->dispatch(UserEvents::USER_PASSWORD, new UserEvent($user, [
                'plainPassword' => $user->getPlainPassword()
            ]));
        }
        
        $this->entityManager->flush();
        
        // Give the credentials back to the user
        $headers = ['Property', 'Value'];
        $rows = [
            ['First Name', $this->user->getFirstName()],
            ['Last Name', $this->user->getLastName()],
            ['E-mail', $this->user->getEmail()],
            ['Nom d\'utilisateur', $this->user->getUsername()],
            ['Mot de passe', $this->plainPassword ? $this->plainPassword : "(inchangé)"],
        ];

        $io->table($headers, $rows);

        return 0;
    }
}