<?php

namespace Nxp\Core\Plugin\Discord;

use Nxp\Core\Interfaces\PluginInterface;
use Nxp\Core\Utils\Webhook\WebhookManager;

class Discord implements PluginInterface
{
    private $manifestData;

    public function execute()
    {
    }

    public function getManifestData(): array
    {
        return $this->manifestData;
    }

    /**
     * Send a Discord webhook message.
     *
     * @param string $webhookURL The URL of the Discord webhook.
     * @param string $message The message content.
     * @param string|null $username The username to display for the webhook message. (Optional)
     * @param string|null $avatarURL The URL of the avatar image to display for the webhook message. (Optional)
     * @return bool True if the webhook was sent successfully, false otherwise.
     * @throws \Exception If there's an error sending the webhook.
     */
    public static function sendMessage($webhookURL, $message, $username = null, $avatarURL = null)
    {
        $data = [
            'content' => $message,
            'username' => $username,
            'avatar_url' => $avatarURL
        ];

        try {
            WebhookManager::sendWebhook($webhookURL, $data);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to send Discord webhook message: " . $e->getMessage());
        }
    }

    /**
     * Send an embed message to a Discord webhook.
     *
     * @param string $webhookURL The URL of the Discord webhook.
     * @param array $embedData The data for the embed message.
     * @param string|null $username The username to display for the webhook message. (Optional)
     * @param string|null $avatarURL The URL of the avatar image to display for the webhook message. (Optional)
     * @return bool True if the webhook was sent successfully, false otherwise.
     * @throws \Exception If there's an error sending the webhook.
     */
    public static function sendEmbedMessage($webhookURL, $embedData, $username = null, $avatarURL = null)
    {
        $data = [
            'embeds' => [$embedData],
            'username' => $username,
            'avatar_url' => $avatarURL
        ];

        try {
            WebhookManager::sendWebhook($webhookURL, $data);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to send Discord embed message: " . $e->getMessage());
        }
    }

    /**
     * Send multiple embed messages to a Discord webhook.
     *
     * @param string $webhookURL The URL of the Discord webhook.
     * @param array $embedDataArray An array of data for multiple embed messages.
     * @param string|null $username The username to display for the webhook message. (Optional)
     * @param string|null $avatarURL The URL of the avatar image to display for the webhook message. (Optional)
     * @return bool True if the webhooks were sent successfully, false otherwise.
     * @throws \Exception If there's an error sending the webhook.
     */
    public static function sendMultipleEmbedMessages($webhookURL, $embedDataArray, $username = null, $avatarURL = null)
    {
        $embeds = [];

        foreach ($embedDataArray as $embedData) {
            $embeds[] = $embedData;
        }

        $data = [
            'embeds' => $embeds,
            'username' => $username,
            'avatar_url' => $avatarURL
        ];

        try {
            WebhookManager::sendWebhook($webhookURL, $data);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to send multiple Discord embed messages: " . $e->getMessage());
        }
    }

    /**
     * Send a file to a Discord webhook.
     *
     * @param string $webhookURL The URL of the Discord webhook.
     * @param string $fileContent The content of the file to send.
     * @param string $fileName The name of the file.
     * @param string|null $username The username to display for the webhook message. (Optional)
     * @param string|null $avatarURL The URL of the avatar image to display for the webhook message. (Optional)
     * @return bool True if the webhook was sent successfully, false otherwise.
     * @throws \Exception If there's an error sending the webhook.
     */
    public static function sendFile($webhookURL, $fileContent, $fileName, $username = null, $avatarURL = null)
    {
        $data = [
            'file' => $fileContent,
            'username' => $username,
            'avatar_url' => $avatarURL
        ];

        $payload = json_encode($data);

        $ch = curl_init($webhookURL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false || ($httpCode !== 200 && $httpCode !== 201 && $httpCode !== 204)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Failed to send file to Discord webhook: $error");
        }

        curl_close($ch);

        return true;
    }

    /**
     * Send a simple text message with mentions to a Discord webhook.
     *
     * @param string $webhookURL The URL of the Discord webhook.
     * @param string $message The message content.
     * @param array|null $mentions An array of user mentions or role mentions. (Optional)
     * @param string|null $username The username to display for the webhook message. (Optional)
     * @param string|null $avatarURL The URL of the avatar image to display for the webhook message. (Optional)
     * @return bool True if the webhook was sent successfully, false otherwise.
     * @throws \Exception If there's an error sending the webhook.
     */
    public static function sendMentionMessage($webhookURL, $message, $mentions = null, $username = null, $avatarURL = null)
    {
        $data = [
            'content' => $message,
            'username' => $username,
            'avatar_url' => $avatarURL,
            'allowed_mentions' => [
                'parse' => ['users', 'roles'],
                'users' => $mentions['users'] ?? [],
                'roles' => $mentions['roles'] ?? []
            ]
        ];

        try {
            WebhookManager::sendWebhook($webhookURL, $data);
            return true;
        } catch (\Exception $e) {
            throw new \Exception("Failed to send mention message to Discord webhook: " . $e->getMessage());
        }
    }

    /**
     * Send a message with an embedded image to a Discord webhook.
     *
     * @param string $webhookURL The URL of the Discord webhook.
     * @param string $message The message content.
     * @param string $imageURL The URL of the image to embed.
     * @param string|null $username The username to display for the webhook message. (Optional)
     * @param string|null $avatarURL The URL of the avatar image to display for the webhook message. (Optional)
     * @return bool True if the webhook was sent successfully, false otherwise.
     * @throws \Exception If there's an error sending the webhook.
     */
    public static function sendImageMessage($webhookURL, $message, $imageURL, $username = null, $avatarURL = null)
    {
        $embedData = [
            'title' => 'Image Message',
            'description' => $message,
            'image' => [
                'url' => $imageURL
            ]
        ];

        return self::sendEmbedMessage($webhookURL, $embedData, $username, $avatarURL);
    }

    /**
     * Send a message with a file attachment to a Discord webhook.
     *
     * @param string $webhookURL The URL of the Discord webhook.
     * @param string $message The message content.
     * @param string $filePath The path to the file to attach.
     * @param string|null $username The username to display for the webhook message. (Optional)
     * @param string|null $avatarURL The URL of the avatar image to display for the webhook message. (Optional)
     * @return bool True if the webhook was sent successfully, false otherwise.
     * @throws \Exception If there's an error sending the webhook.
     */
    public static function sendFileAttachment($webhookURL, $message, $filePath, $username = null, $avatarURL = null)
    {
        $fileContent = base64_encode(file_get_contents($filePath));
        $fileName = basename($filePath);

        return self::sendFile($webhookURL, $fileContent, $fileName, $username, $avatarURL);
    }

    /**
     * Send a message with multiple lines (line breaks) to a Discord webhook.
     *
     * @param string $webhookURL The URL of the Discord webhook.
     * @param string $message The message content with line breaks.
     * @param string|null $username The username to display for the webhook message. (Optional)
     * @param string|null $avatarURL The URL of the avatar image to display for the webhook message. (Optional)
     * @return bool True if the webhook was sent successfully, false otherwise.
     * @throws \Exception If there's an error sending the webhook.
     */
    public static function sendMultilineMessage($webhookURL, $message, $username = null, $avatarURL = null)
    {
        $message = str_replace("\n", "\n\n", $message);
        return self::sendMessage($webhookURL, $message, $username, $avatarURL);
    }

    /**
     * Send a message with a custom timestamp to a Discord webhook.
     *
     * @param string $webhookURL The URL of the Discord webhook.
     * @param string $message The message content.
     * @param \DateTime $timestamp The custom timestamp to display for the message. (Optional)
     * @param string|null $username The username to display for the webhook message. (Optional)
     * @param string|null $avatarURL The URL of the avatar image to display for the webhook message. (Optional)
     * @return bool True if the webhook was sent successfully, false otherwise.
     * @throws \Exception If there's an error sending the webhook.
     */
    public static function sendTimestampedMessage($webhookURL, $message, \DateTime $timestamp = null, $username = null, $avatarURL = null)
    {
        $embedData = [
            'title' => 'Timestamped Message',
            'description' => $message,
            'timestamp' => $timestamp ? $timestamp->format(\DateTime::ISO8601) : null
        ];

        return self::sendEmbedMessage($webhookURL, $embedData, $username, $avatarURL);
    }

    /**
     * Send a message with a custom footer to a Discord webhook.
     *
     * @param string $webhookURL The URL of the Discord webhook.
     * @param string $message The message content.
     * @param string $footerText The text to display in the footer of the message. (Optional)
     * @param string|null $footerIconURL The URL of the icon image to display in the footer. (Optional)
     * @param string|null $username The username to display for the webhook message. (Optional)
     * @param string|null $avatarURL The URL of the avatar image to display for the webhook message. (Optional)
     * @return bool True if the webhook was sent successfully, false otherwise.
     * @throws \Exception If there's an error sending the webhook.
     */
    public static function sendFooterMessage($webhookURL, $message, $footerText = null, $footerIconURL = null, $username = null, $avatarURL = null)
    {
        $embedData = [
            'title' => 'Footer Message',
            'description' => $message,
            'footer' => [
                'text' => $footerText,
                'icon_url' => $footerIconURL
            ]
        ];

        return self::sendEmbedMessage($webhookURL, $embedData, $username, $avatarURL);
    }
}
