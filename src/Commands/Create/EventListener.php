<?php

declare(strict_types=1);

namespace Nextcloud\DevCli\Commands\Create;

use Nextcloud\DevCli\Generator\FileCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

class EventListener extends Command {
	use CreateTrait;
	public function __construct(private FileCreator $fileCreator) {
		parent::__construct('create:listener');

		$this
			->setDescription('Creates a new event listener class')
			->addArgument('className', InputArgument::REQUIRED)
			->addArgument('events', InputArgument::IS_ARRAY);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$this->setInputOutput($input, $output);
		$className = $input->getArgument('className');
		$events = $input->getArgument('events');
		$classFqn = $this->fileCreator->buildNewClassNamespace($className, 'Listener', 'EventListener');

		$io = new SymfonyStyle($input, $output);

		if ($events === []) {
			$continueAsking = true;
			while ($continueAsking) {
				$helper = $this->getHelper('question');
				$question = new ChoiceQuestion(
					'Please select the event to listen to:',
					['Done', ...array_filter($this->getAvailableEvents(), function ($event) use ($events) {
						return !in_array($event, $events);
					})],
					0
				);
				$question->setAutocompleterValues($this->getAvailableEvents());
				$question->setErrorMessage('Color %s is invalid.');
				$event = $helper->ask($input, $output, $question);
				if ($event === 'Done') {
					$continueAsking = false;
				} else {
					$events[] = $event;
				}
				$io->write(sprintf("\033\143"));
				$io->title('File preview ' . $classFqn);
				$io->block(
					$this->fileCreator->createClassFromStub('EventListener', $classFqn, ['events' => $events ]),
					null, 'fg=gray', '  ', false
				);

			}
		}

		return $this->writeClass($className, 'EventListener', ['events' => $events]);
	}

	public function getAvailableEvents() {
		// docs: rg Event | grep -oE '``([A-z\\]*)``' | sed 's/`//g' | grep -E "(OCP|OCA)" | sed 's/^\\//g'
		// TODO: Figure out a smart way to get available events from server/apps
		return [
			'OCA\DAV\Events\CardCreatedEvent',
			'OCA\DAV\Events\CardUpdatedEvent',
			'OCA\DAV\Events\CardDeletedEvent',
		];
	}
}
