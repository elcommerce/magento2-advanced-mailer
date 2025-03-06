<?php
declare(strict_types=1);

namespace Elcommerce\AdvancedMailer\Model\Mail\Template;

use Laminas\Mime\Part as LaminasMimePart;
use Laminas\Mime\Message as LaminasMimeMessage;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\MessageInterface;
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
            sprintf("Part must be an instance of %s or %s", MimePart::class, LaminasMimePart::class)
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
        if ($this->message instanceof EmailMessageInterface &&
            $this->message instanceof MessageInterface) {
            /** @var MimePartInterface[] $parts */
            $parts = $this->message->getMessageBody()->getParts();
            $mimeMessage = new LaminasMimeMessage();
            $mimeMessage->setParts(array_merge($parts, $this->additionalMimeParts));
            $this->message->setBody($mimeMessage);
            return $this;
        }
        throw new TransportBuilderException(
            sprintf("Message should implement both %s and %s",
                EmailMessageInterface::class,
                MessageInterface::class
            )
        );
    }
}
