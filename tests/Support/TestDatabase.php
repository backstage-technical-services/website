<?php

namespace Tests\Support;

use RuntimeException;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Modules\MariaDBContainer;
use Testcontainers\Wait\WaitForLog;
use Throwable;

/**
 * The database that the tests run against: MariaDB 10.4, the version that production runs, in a container that the
 * tests start themselves with Testcontainers. It starts when the first test creates the application and is removed
 * when the tests finish, so the tests only ever use a database that they created.
 *
 * Testcontainers for PHP doesn't remove containers if the tests are killed (for example with Ctrl-C), so this also
 * starts Ryuk, which removes the database a few seconds after the tests' connection to it closes. Set
 * TESTCONTAINERS_RYUK_DISABLED=true to turn it off, for example where Ryuk can't use the Docker socket.
 */
final class TestDatabase
{
    private const DATABASE = 'backstage';

    private const USERNAME = 'test';

    private const PASSWORD = 'test';

    /**
     * @var array{host: string, port: int, database: string, username: string, password: string}|null
     */
    private static ?array $connection = null;

    /**
     * Why the database couldn't be started, so that the rest of the tests fail straight away instead of each trying
     * again.
     */
    private static ?Throwable $failure = null;

    /**
     * Start the database, if it isn't running yet, and get the details to connect to it.
     *
     * @return array{host: string, port: int, database: string, username: string, password: string}
     */
    public static function connection(): array
    {
        if (self::$failure !== null) {
            throw self::$failure;
        }

        try {
            return self::$connection ??= self::start();
        } catch (Throwable $exception) {
            throw (self::$failure = $exception);
        }
    }

    /**
     * @return array{host: string, port: int, database: string, username: string, password: string}
     */
    private static function start(): array
    {
        $labels = ['bts-v4-tests' => bin2hex(random_bytes(8))];
        $ryuk = self::startRyuk($labels);
        $container = new MariaDBContainer('10.4')
            ->withMariaDBDatabase(self::DATABASE)
            ->withMariaDBUser(self::USERNAME, self::PASSWORD)
            ->withTmpfs('/var/lib/mysql')
            ->withLabels($labels)
            ->withAutoRemove(true)
            ->start();

        register_shutdown_function(function () use ($container, $ryuk) {
            $container->stop();
            if ($ryuk !== null) {
                fclose($ryuk);
            }
        });

        return [
            'host' => $container->getHost(),
            'port' => $container->getFirstMappedPort(),
            'database' => self::DATABASE,
            'username' => self::USERNAME,
            'password' => self::PASSWORD,
        ];
    }

    /**
     * Start Ryuk and tell it to remove the containers with these labels once the returned connection closes, which
     * happens when the tests finish or are killed.
     *
     * @param array<string, string> $labels
     *
     * @return resource|null
     */
    private static function startRyuk(array $labels): mixed
    {
        if (filter_var(getenv('TESTCONTAINERS_RYUK_DISABLED'), FILTER_VALIDATE_BOOL)) {
            return null;
        }

        $ryuk = new GenericContainer('testcontainers/ryuk:0.12.0')
            ->withExposedPorts(8080)
            ->withMount(
                getenv('TESTCONTAINERS_DOCKER_SOCKET_OVERRIDE') ?: '/var/run/docker.sock',
                '/var/run/docker.sock',
            )
            ->withAutoRemove(true)
            ->withWait(new WaitForLog('msg=Started'))
            ->start();

        $connection = fsockopen($ryuk->getHost(), $ryuk->getFirstMappedPort(), $errorCode, $error, 10);
        $filters = implode('&', array_map(fn($key, $value) => "label={$key}={$value}", array_keys($labels), $labels));
        if ($connection === false || fwrite($connection, "{$filters}\n") === false || fgets($connection) !== "ACK\n") {
            throw new RuntimeException("Couldn't connect to Ryuk: {$error}");
        }

        return $connection;
    }
}
