<?php

namespace App\Command;

// src/Command/CreateUserCommand.php
namespace App\Command;

use App\Service\Scrape;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the "name" and "description" arguments of AsCommand replace the
// static $defaultName and $defaultDescription properties
#[AsCommand(
  name: 'app:scrape',
  description: 'Runs the diagram scraper.',
  hidden: false
)]
class ScrapeCommand extends Command
{

  /**
   * @var \App\Service\Scrape
   */
  private Scrape $scrape;

  public function __construct(Scrape $scrape, string $name = null)
  {
    parent::__construct($name);
    $this->scrape = $scrape;
  }

  public function configure(): void
  {
    $this->addArgument('models', InputArgument::REQUIRED, 'Comma separated list of models');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $models = explode(",", $input->getArgument("models"));
    print_r($models);
    if(count($models) < 1){
      $output->writeln("Please include at least one model to scrape.");
      die();
    }
    $output->writeln('Scrape run successfully!');
    return Command::SUCCESS;
  }


}
