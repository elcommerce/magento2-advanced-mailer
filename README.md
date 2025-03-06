# Advanced Mailer extension for Magento 2

## What for this extension is designed for?

It's impossible currently to send emails with file attachments from Magento 2 class called:
```
Magento\Framework\Mail\Template\TransportBuilder
```

There were some community PRs: https://github.com/magento/magento2/pull/33262

But for some starnge reasons Adobe doesn't accept such useful changes from community members. That's weird.

``elcommerce/magento2-advanced-mailer`` is FREE of:
- PHP ```Reflection``` API usage
- big and ugly rewriting of ```TransportBuilder``` with huge ``__constructor()`` overload

## Compatibility

- Magento: v2.4.0 and later
- PHP: 7.4+

## Installation
install module using composer:
```
composer require elcommerce/magento2-advanced-mailer
```
then do:
```
bin/magento setup:upgrade
bin/magento setup:di:compile
```
## API
There's an override in ``di.xml``, so your ``TransportBuilder`` objects will have ``addAttachment()`` method now:
```php
public function addAttachment(
    string $content,
    string $fileName,
    ?string $fileType = MimeInterface::TYPE_OCTET_STREAM
): TransportBuilder
```
As bonus you have ``addMimePart()`` method too:
```php
public function addMimePart(object $part): TransportBuilder
```
Where ``$part`` is ``Magento\Framework\Mail\MimePart`` or ``Laminas\Mime\Part`` instance





