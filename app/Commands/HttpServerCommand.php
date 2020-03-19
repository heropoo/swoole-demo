<?php
/**
 * Date: 2020-03-19
 * Time: 14:08
 */

namespace App\Commands;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HttpServerCommand extends Command
{
    protected static $defaultName = 'http-server';
    protected $master_pid_file;

    protected function configure()
    {
        $this->master_pid_file = ROOT_PATH . '/runtime/' . self::$defaultName . '.pid';

        // the short description shown while running "php bin/console list"
        $this->setDescription('Start a swoole http server')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command can start a swoole http server');

        $this->addArgument('action', InputArgument::REQUIRED, 'start|stop|reload|status');
        $this->addOption('host', null, InputOption::VALUE_OPTIONAL, 'tcp host', '127.0.0.1');
        $this->addOption('port', null, InputOption::VALUE_OPTIONAL, 'port', '8000');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');

        $host = $input->getOption('host');
        $port = $input->getOption('port');

        if ($action == 'start') {

            $server = new Server($host, $port);
            $server->set(['daemonize' => true]);

            $server->on('request', function (Request $request, Response $response) {
                // var_dump($request->cookie);

                //$app->handleSwooleRequest($request, $response);
                $response->end(var_export($request->server, 1));
            });

            $server->on('start', function (Server $server) use ($host, $port) {
                echo "Http server listening on http://$host:$port , you can open url http://127.0.0.1:$port in browser.\n";
                //var_dump($server->master_pid);
                file_put_contents($this->master_pid_file, $server->master_pid);
            });

            $server->on('shutdown', function () {
                echo "Http server is shutdown \n";
                unlink($this->master_pid_file);
            });

            $server->start();
        } else if ($action == 'stop') {
            $master_pid = file_get_contents($this->master_pid_file);
            if ($master_pid) {
                $res = exec("kill $master_pid", $output, $return_var);
                var_dump($res, $output, $return_var);
                if ($return_var === 0) {
                    echo "Http server is stop\n";
                }
            }
        }
//        var_dump($input->getArguments());
//        var_dump($input->getOptions());

        return 0;
    }
}