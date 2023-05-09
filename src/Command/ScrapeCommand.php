<?php

namespace App\Command;

use App\Service\Scrape;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

  /**
   * Constructor, pass in the scrape service
   * @param  \App\Service\Scrape  $scrape
   * @param  string|null  $name
   */
  public function __construct(Scrape $scrape, string $name = null)
  {
    parent::__construct($name);
    $this->scrape = $scrape;
  }

  /**
   * Configure the console command
   * @return void
   */
  public function configure(): void
  {
    $this->addArgument('models', InputArgument::REQUIRED, 'Comma separated list of models');
  }

  /**
   * Execute console command
   * @param  \Symfony\Component\Console\Input\InputInterface  $input
   * @param  \Symfony\Component\Console\Output\OutputInterface  $output
   * @return int
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    // Grab the equipment models from the command line, comma separated
    $models = explode(",", $input->getArgument("models"));
    if(count($models) < 1){
      $output->writeln("Please include at least one model to scrape.");
      die();
    }
    $output->writeln('Scrape run successfully!');
    return Command::SUCCESS;
  }
}
