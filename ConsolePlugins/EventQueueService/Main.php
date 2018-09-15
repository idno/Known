<?php

namespace ConsolePlugins\EventQueueService {

    class Main extends \Idno\Common\ConsolePlugin {
        
        public static $run = true;
               
        public function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output) {
            
            $queue = $input->getArgument('queue');
            $pollperiod = (int)$input->getArgument('pollperiod');
            
            define("KNOWN_EVENT_QUEUE_SERVICE", true);
                    
            // Set up shutdown listener
            
            pcntl_signal(SIGTERM, function($signo) {
                \Idno\Core\Idno::site()->logging()->debug('SIGTERM received, shutting down.');
                \ConsolePlugins\EventQueueService\Main::$run = false;
                \Idno\Core\Idno::site()->logging()->info('Shutting down, this may take a little while...');
            });
            
            try {
                $pid = pcntl_fork();
                if ($pid == -1) {
                     throw new \RuntimeException("Could not fork a new process");
                } else if ($pid) {
                    \Idno\Core\Idno::site()->logging()->info('Starting GC thread for ' . $queue);

                    try {
                        while(self::$run) {
                            sleep(300);
                            
                            \Idno\Core\Service::call('/service/queue/gc/');
                            
                        }
                    } catch (\Error $e) {
                        \Idno\Core\Idno::site()->logging()->error($e->getMessage());
                    }

                } else {
                    \Idno\Core\Idno::site()->logging()->info('Starting Asynchronous event processor on queue: ' . $queue. ", polling every $pollperiod seconds");

                    while (self::$run) {
                        
                        try {
                            // Reinitialise DB
                            $this->reinitialiseDB();

                            while(self::$run) {

                                \Idno\Core\Idno::site()->logging()->debug('Polling queue...');
                                
                                if ($events = \Idno\Core\Service::call('/service/queue/list/')) {
                                    foreach ($events->queue as $event) {
                                        try {
                                            \Idno\Core\Idno::site()->logging()->info("Dispatching event $event");
                                            \Idno\Core\Service::call('/service/queue/dispatch/' . $event);
                                        } catch (\Exception $ex) {
                                            \Idno\Core\Idno::site()->logging()->error($ex->getMessage());
                                        }
                                    }
                                }

                                sleep($pollperiod);
                            }
                        
                        } catch (\Error $e) {
                            \Idno\Core\Idno::site()->logging()->error($e->getMessage());
                        }                    
                    }
                } 
            } catch (\Exception $e) {
                \Idno\Core\Idno::site()->logging()->error($e->getMessage());
            }
       }

        public function getCommand() {
            return 'service-event-queue';
        }

        public function getDescription() {
            return 'Begin the Asynchronous event queue dispatcher service';
        }

        public function getParameters() {
            return [
                new \Symfony\Component\Console\Input\InputArgument('queue', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Queue to process', 'default'),
                new \Symfony\Component\Console\Input\InputArgument('pollperiod', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'How often should the service poll the queue', 20),
            ];
        }

    }

}