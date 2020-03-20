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
    protected $log_file;

    protected function configure()
    {
        $this->master_pid_file = ROOT_PATH . '/runtime/' . self::$defaultName . '.pid';
        $this->log_file = ROOT_PATH . '/runtime/logs/' . self::$defaultName . '.log';
        if (!is_dir(ROOT_PATH . '/runtime/logs')) {
            mkdir(ROOT_PATH . '/runtime/logs');
        }

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
            //$server->set(['worker_num' => 4]);
            $server->set(['log_file' => $this->log_file]);

            $server->on('request', function (Request $request, Response $response) {
                // var_dump($request->cookie);

                //$app->handleSwooleRequest($request, $response);
                $response->end(var_export($request->server, 1));
            });

            $server->on('start', function (Server $server) use ($host, $port) {
                echo "Http server listening on http://$host:$port , you can open url http://127.0.0.1:$port in browser.\n";
                //var_dump($server->master_pid);
                file_put_contents($this->master_pid_file, $server->master_pid);

                var_dump($server->master_pid, $server->manager_pid);
            });

            $server->on('shutdown', function () {
                echo "Http server is shutdown \n";
                unlink($this->master_pid_file);
            });

            $server->start();
        } else if ($action == 'stop') {
            $master_pid = file_exists($this->master_pid_file) ? file_get_contents($this->master_pid_file) : 0;
            if ($master_pid) {
                $res = exec("kill $master_pid", $output, $return_var);
                //var_dump($res, $output, $return_var);
                if ($return_var === 0) {
                    echo "Http server is stop\n";
                } else {
                    echo "Http server is failed to stop\n";
                }
            }
        } else if ($action == 'status') {
            $master_pid = file_exists($this->master_pid_file) ? file_get_contents($this->master_pid_file) : 0;

            $res = exec("ps aux|grep " . self::$defaultName . "|grep -v grep|grep -v status|awk '{print $2}'", $output, $return_var);
//            var_dump($res, $output, $return_var);
            if ($return_var === 0 && $master_pid > 0 && in_array($master_pid, $output)) {
                sort($output, SORT_ASC);
                var_dump($output);
                echo "Http server is running\n";
            } else {
                echo "Http server is not running\n";
            }
        } else if ($action == 'reload') {

        }
//        var_dump($input->getArguments());
//        var_dump($input->getOptions());

        return 0;
    }
}