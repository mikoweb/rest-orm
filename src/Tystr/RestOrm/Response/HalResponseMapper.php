<?php

namespace Tystr\RestOrm\Response;

use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Nocarrier\Hal;
use Psr\Http\Message\ResponseInterface;
use Tystr\RestOrm\Exception\InvalidArgumentException;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
class HalResponseMapper implements ResponseMapperInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer = null)
    {
        $this->serializer = $serializer ?: SerializerBuilder::create()->build();
    }

    /**
     * Maps a response body to an object
     *
     * @param ResponseInterface $response
     * @param string            $class
     * @param string            $format
     *
     * @return object
     */
    public function map(ResponseInterface $response, $class, $format)
    {
        if ('json' === $format) {
            $hal = Hal::fromJson((string) $response->getBody(), 10);
        } elseif ('xml' === $format) {
            $hal = Hal::fromXml((string) $response->getBody(), 10);
        } else {
            throw new InvalidArgumentException(sprintf('Unsupported format "%s".', $format));
        }

        return $this->serializer->fromArray($this->getDataFromHal($hal, $class), $class);
    }

    /**
     * Attempts to extra the model's data from the HAL representation. If there are embedded resources, we assume that
     * this is a collection endpoint and we should use the embedded resources as the data. Otherwise, we use the root
     * fields as the data.
     *
     * @todo This is probably not a safe assumption to make
     *
     * @param Hal     $hal
     * @param string $class
     *
     * @return array
     */
    protected function getDataFromHal(Hal $hal, &$class)
    {
        $data = [];
        $embeddedResources = $hal->getResources();
        if (count($embeddedResources) > 0) {
            foreach ($embeddedResources as $resources) {
                foreach ($resources as $hal) {
                    $data[] = $hal->getData();
                }
            }
            // @todo Fix this hack makes sure we only wrap in array<> if the Manager hasn't already. This is because
            // The StandardResponseMapper is not aware if the request is for a collection or not, so the manager handles
            // wrapping the type in array<>
            if (substr($class, 0, 6) !== 'array<') {
                $class = sprintf('array<%s>', $class);
            }
        } else {
            $data = $hal->getData();
        }

        return $data;
    }
}
