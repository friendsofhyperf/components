<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\GatewayWorker\Command;

use FriendsOfHyperf\GatewayWorker\GatewayWorkerEventInterface;
use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Hyperf\Command\Command;
use LogicException;
use Workerman\Events\EventInterface;
use Workerman\Worker;

use function Hyperf\Config\config;

class ServeCommand extends Command
{
    protected ?string $signature = 'gateway-worker:serve
    {action=start : start|stop|restart|reload|status|connections|help}
    {--daemon}

    {--register : Enable register service}
    {--register-bind= : Bind host and port for register}
    {--register-processes= : Process num for register}

    {--register-address= : Register address for gateway or business worker}

    {--gateway : Enable gateway service}
    {--gateway-bind= : Bind host and port for gateway}
    {--gateway-processes= : Process num for gateway}
    {--lan-ip=127.0.0.1 : Lan IP}

    {--businessworker : Enable business worker service}
    {--businessworker-processes= : Process num for business worker}
    ';

    protected string $description = 'Gateway Worker Service.';

    public function handle()
    {
        if (! in_array($action = $this->input->getArgument('action'), ['start', 'stop', 'restart', 'reload', 'status', 'connections', 'help'])) {
            $this->error('Error Arguments');
            exit;
        }

        if ($this->input->getOption('register')) {
            $registerBind = $this->input->getOption('register-bind') ?? config('gatewayworker.register.bind', '0.0.0.0:1215');
            $registerProcesses = $this->input->getOption('register-processes') ?? config('gatewayworker.register.processes', 1);

            $this->info('Register:');
            $this->table(['Argument', 'Value'], [
                ['bind', $registerBind],
                ['processes', $registerProcesses],
            ]);

            $register = new Register('text://' . $registerBind);
            $register->name = config('gatewayworker.register.name', 'Register');
            $register->count = $registerProcesses;
        }

        if ($this->input->getOption('gateway')) {
            $registerAddress = $this->input->getOption('register-address') ?? config('gatewayworker.register_address', '127.0.0.1:1215');
            $gatewayBind = $this->input->getOption('gateway-bind') ?? config('gatewayworker.gateway.bind', '0.0.0.0:1216');
            $gatewayProcesses = $this->input->getOption('gateway-processes') ?? config('gatewayworker.gateway.processes', 1);
            $lanIp = $this->input->getOption('lan-ip') ?? config('gatewayworker.gateway.lan_ip', '');
            $pingData = config('gatewayworker.gateway.ping_data', '{"mode":"heart"}');

            $this->info('Gateway:');
            $this->table(['Argument', 'Value'], [
                ['bind', $gatewayBind],
                ['processes', $gatewayProcesses],
                ['register_address', $registerAddress],
                ['lan_ip', $lanIp],
                ['ping_data', $pingData],
            ]);

            $gateway = new Gateway('websocket://' . $gatewayBind);
            $gateway->name = config('gatewayworker.gateway.name', 'Gateway');
            $gateway->count = $gatewayProcesses;
            $gateway->lanIp = $lanIp;
            $gateway->startPort = config('gatewayworker.gateway.start_port', 2300);
            $gateway->pingInterval = config('gatewayworker.gateway.ping_interval', 30);
            $gateway->pingNotResponseLimit = config('gatewayworker.gateway.ping_not_response_limit', 0);
            $gateway->pingData = $pingData;
            $gateway->registerAddress = $registerAddress;
        }

        if ($this->input->getOption('businessworker')) {
            $registerAddress = $this->input->getOption('register-address') ?? config('gatewayworker.register_address', '127.0.0.1:1215');
            $businessworkerProcesses = $this->input->getOption('businessworker-processes') ?? config('gatewayworker.businessworker.processes', 1);
            $eventHandler = config('gatewayworker.businessworker.event_handler', '');

            $this->info('BusinessWorker:');
            $this->table(['Argument', 'Value'], [
                ['processes', $businessworkerProcesses],
                ['register_address', $registerAddress],
                ['event_handler', $eventHandler],
            ]);

            $worker = new BusinessWorker();
            $worker->name = config('gatewayworker.businessworker.name', 'BusinessWorker');
            $worker->count = $businessworkerProcesses;
            $worker->registerAddress = $registerAddress;

            if ($eventHandler) {
                if (! class_exists($eventHandler)) {
                    throw new LogicException("Event '{$eventHandler}' is not exists", 1);
                }

                if (! in_array(GatewayWorkerEventInterface::class, (array) class_implements($eventHandler))) {
                    throw new LogicException("{$eventHandler} must implements of " . GatewayWorkerEventInterface::class, 1);
                }

                $worker->eventHandler = $eventHandler;
            }
        }

        global $argv;
        $argv[0] = 'gateway-worker:server';
        $argv[1] = $action;
        $argv[2] = $this->input->getOption('daemon') ? '-d' : '';

        Worker::$pidFile = config('gatewayworker.pid_file', BASE_PATH . '/runtime/workerman.pid');
        Worker::$logFile = config('gatewayworker.log_file', BASE_PATH . '/runtime/logs/workerman.log');

        $eventLoopClass = config('gatewayworker.event_loop_class');

        if (class_exists($eventLoopClass) && in_array(EventInterface::class, (array) class_implements($eventLoopClass))) {
            Worker::$eventLoopClass = $eventLoopClass;
        }

        Worker::runAll();
    }
}
