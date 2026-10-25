<?php

namespace Tests\Support;

use Docker\API\Model\ContainersCreatePostBody;
use Testcontainers\Container\GenericContainer;
use Testcontainers\Wait\WaitForLog;

/**
 * Ryuk, which removes the test database if the tests are killed (see TestDatabase).
 *
 * Testcontainers for PHP asks Docker to publish a container's ports, but doesn't expose them. Docker 29 publishes them
 * anyway, but earlier versions (such as 28, which GitHub's runners have) only publish ports that are exposed, and
 * Ryuk's image doesn't expose any. So this exposes them as well.
 */
final class RyukContainer extends GenericContainer
{
    public const PORT = 8080;

    public function __construct()
    {
        parent::__construct('testcontainers/ryuk:0.12.0');

        $this->withExposedPorts(self::PORT)
            ->withMount(
                getenv('TESTCONTAINERS_DOCKER_SOCKET_OVERRIDE') ?: '/var/run/docker.sock',
                '/var/run/docker.sock',
            )
            ->withAutoRemove(true)
            ->withWait(new WaitForLog('msg=Started'));
    }

    protected function createContainerConfig(): ContainersCreatePostBody
    {
        return parent::createContainerConfig()->setExposedPorts(array_fill_keys($this->exposedPorts, []));
    }
}
