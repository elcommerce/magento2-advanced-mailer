<?php
declare(strict_types=1);

namespace Elcommerce\AdvancedMailer\Model\Mail\Template;

use ReflectionClass;
use ReflectionException;
use Laminas\Mime\Part as LaminasMimePart;
use Laminas\Mime\Message as LaminasMimeMessage;
use Laminas\Mail\Message as LaminasMailMessage;
use Magento\Framework\Mail\MimeInterface;
use Magento\Framework\Mail\MimePart;
use Magento\Framework\Mail\Template\TransportBuilder as ParentTransportBuilder;

/**
 *
 */
class TransportBuilder extends ParentTransportBuilder
{
    /**
     * @var array
     */
    protected array $additionalMimeParts = [];

    /**
     * @var string
     */
    protected string $zendMessageProperty = 'zendMessage';

    /**
     * @return TransportBuilder
     */
    protected function reset()
    {
        $this->additionalMimeParts = [];
        return parent::reset();
    }

    /**
     * @param LaminasMimePart|MimePart $part
     * @return $this
     * @throws TransportBuilderException
     */
    public function addMimePart(object $part): TransportBuilder
    {
        if ($part instanceof MimePart || $part instanceof LaminasMimePart) {
            $this->additionalMimeParts[] = $part;
            return $this;
        }
        throw new TransportBuilderException(
            __("Part must be an instance of %1 or %2", MimePart::class, LaminasMimePart::class)->render()
        );
    }

    /**
     * @param string $content
     * @param string $fileName
     * @param string|null $fileType
     * @return $this
     * @throws TransportBuilderException
     */
    public function addAttachment(
        string $content,
        string $fileName,
        ?string $fileType = MimeInterface::TYPE_OCTET_STREAM
    ): TransportBuilder {
        $mimePart = new LaminasMimePart();
        $mimePart->setContent($content)
            ->setType($fileType)
            ->setFilename($fileName)
            ->setDisposition(MimeInterface::DISPOSITION_ATTACHMENT)
            ->setEncoding(MimeInterface::ENCODING_BASE64);
        $this->addMimePart($mimePart);
        return $this;
    }

    /**
     * @return $this|TransportBuilder
     * @throws TransportBuilderException
     */
    protected function prepareMessage()
    {
        parent::prepareMessage();
        if (!$this->additionalMimeParts) {
            return $this;
        }
        $body = $this->extractZendMessageBody();
        $parts = array_merge($body->getParts(), $this->additionalMimeParts);
        $body->setParts($parts);
        $this->message->setBody($body);
        return $this;
    }

    /**
     * @return LaminasMailMessage
     * @throws ReflectionException
     */
    protected function extractZendMessage(): LaminasMailMessage
    {
        try {
            $reflection = new ReflectionClass($this->message);
            $property = $reflection->getProperty($this->zendMessageProperty);
            $property->setAccessible(true);
            return $property->getValue($this->message);
        } catch (ReflectionException $e) {
            throw new TransportBuilderException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return LaminasMimeMessage
     * @throws TransportBuilderException
     */
    protected function extractZendMessageBody(): LaminasMimeMessage
    {
        $body = $this->extractZendMessage()->getBody();
        if (!$body instanceof LaminasMimeMessage) {
            throw new TransportBuilderException(
                __("Message body is expected to be instance of %1", LaminasMimeMessage::class)->render()
            );
        }
        return $body;
    }
}
