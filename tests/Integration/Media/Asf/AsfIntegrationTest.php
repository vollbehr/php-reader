<?php

declare(strict_types=1);

namespace Vollbehr\Tests\Integration\Media\Asf;

use PHPUnit\Framework\TestCase;
use Vollbehr\Io\StringWriter;
use Vollbehr\Media\Asf;
use Vollbehr\Media\Asf\BaseObject;
use Vollbehr\Media\Asf\Object\FileProperties;
use Vollbehr\Media\Asf\Object\StreamProperties;

/**
 * @coversNothing
 */
final class AsfIntegrationTest extends TestCase
{
    public function testParsesMinimalAsfContainer(): void
    {
        $binary = $this->buildMinimalAsfFile();
        $path   = tempnam(sys_get_temp_dir(), 'asf');
        self::assertIsString($path);

        try {
            file_put_contents($path, $binary);

            $asf    = new Asf($path);
            $header = $asf->getHeader();

            $fileProperties = $header->getObjectsByIdentifier(BaseObject::FILE_PROPERTIES)[0];
            self::assertSame(strlen($binary), $fileProperties->getFileSize());

            $streamProperties = $header->getObjectsByIdentifier(BaseObject::STREAM_PROPERTIES);
            self::assertCount(1, $streamProperties);

            $data = $asf->getData();
            self::assertSame(0, $data->getTotalDataPackets());
        } finally {
            @unlink($path);
        }
    }

    public function testWritePersistsIndexParameters(): void
    {
        $binary     = $this->buildAsfFileWithIndexParameters();
        $inputPath  = tempnam(sys_get_temp_dir(), 'asf');
        $outputPath = tempnam(sys_get_temp_dir(), 'asf');
        self::assertIsString($inputPath);
        self::assertIsString($outputPath);

        try {
            file_put_contents($inputPath, $binary);

            $asf = new Asf($inputPath);
            $asf->write($outputPath);

            $output = new Asf($outputPath);
            $header = $output->getHeader();

            $headerExtension = $header->getObjectsByIdentifier(BaseObject::HEADER_EXTENSION)[0];
            $indexParameters = $headerExtension->getObjectsByIdentifier(BaseObject::INDEX_PARAMETERS)[0];

            self::assertSame(5000, $indexParameters->getIndexEntryTimeInterval());
            self::assertSame([
                ['streamNumber' => 1, 'indexType' => 3],
            ], $indexParameters->getIndexSpecifiers());
        } finally {
            @unlink($inputPath);
            @unlink($outputPath);
        }
    }

    private function buildMinimalAsfFile(): string
    {
        $fileId = '00000000-0000-0000-0000-000000000000';

        $filePropertiesPayload = new StringWriter();
        $filePropertiesPayload->writeGuid($fileId)
            ->writeInt64LE(0) // placeholder for file size
            ->writeInt64LE(0) // creation date
            ->writeInt64LE(0) // data packets
            ->writeInt64LE(0) // play duration
            ->writeInt64LE(0) // send duration
            ->writeInt64LE(0) // preroll
            ->writeUInt32LE(FileProperties::SEEKABLE)
            ->writeUInt32LE(1) // minimum data packet size
            ->writeUInt32LE(1) // maximum data packet size
            ->writeUInt32LE(0); // maximum bitrate
        $fileProperties = $this->wrapObject(BaseObject::FILE_PROPERTIES, $filePropertiesPayload->toString());

        $typeSpecific = new StringWriter();
        $typeSpecific->writeUInt16LE(0x0055) // codec id (MPEG Layer 3)
            ->writeUInt16LE(2) // channels
            ->writeUInt32LE(44100)
            ->writeUInt32LE(16000) // average bytes per second
            ->writeUInt16LE(1) // block alignment
            ->writeUInt16LE(16) // bits per sample
            ->writeUInt16LE(0); // codec specific size
        $streamPropertiesPayload = new StringWriter();
        $streamPropertiesPayload->writeGuid(StreamProperties::AUDIO_MEDIA)
            ->writeGuid(StreamProperties::NO_ERROR_CORRECTION)
            ->writeInt64LE(0) // time offset
            ->writeUInt32LE($typeSpecific->getSize())
            ->writeUInt32LE(0) // error correction data length
            ->writeUInt16LE(0x0001) // flags (stream number 1)
            ->writeUInt32LE(0) // reserved
            ->write($typeSpecific->toString());
        $streamProperties = $this->wrapObject(BaseObject::STREAM_PROPERTIES, $streamPropertiesPayload->toString());

        $headerExtensionPayload = new StringWriter();
        $headerExtensionPayload->writeGuid($fileId)
            ->writeUInt16LE(0)
            ->writeUInt32LE(0);
        $headerExtension = $this->wrapObject(BaseObject::HEADER_EXTENSION, $headerExtensionPayload->toString());

        $headerPayload = new StringWriter();
        $headerPayload->writeUInt32LE(3)
            ->writeInt8(1)
            ->writeInt8(2)
            ->write($fileProperties)
            ->write($streamProperties)
            ->write($headerExtension);
        $header = $this->wrapObject(BaseObject::HEADER, $headerPayload->toString());

        $dataPayload = new StringWriter();
        $dataPayload->writeGuid($fileId)
            ->writeInt64LE(0) // total data packets
            ->writeUInt16LE(0);
        $data = $this->wrapObject(BaseObject::DATA, $dataPayload->toString());

        $file = $header . $data;

        return $this->patchFileSize($file, strlen($file));
    }

    private function buildAsfFileWithIndexParameters(): string
    {
        $fileId = '00000000-0000-0000-0000-000000000000';

        $filePropertiesPayload = new StringWriter();
        $filePropertiesPayload->writeGuid($fileId)
            ->writeInt64LE(0)
            ->writeInt64LE(0)
            ->writeInt64LE(0)
            ->writeInt64LE(0)
            ->writeInt64LE(0)
            ->writeInt64LE(0)
            ->writeUInt32LE(FileProperties::SEEKABLE)
            ->writeUInt32LE(1)
            ->writeUInt32LE(1)
            ->writeUInt32LE(0);
        $fileProperties = $this->wrapObject(BaseObject::FILE_PROPERTIES, $filePropertiesPayload->toString());

        $typeSpecific = new StringWriter();
        $typeSpecific->writeUInt16LE(0x0055)
            ->writeUInt16LE(2)
            ->writeUInt32LE(44100)
            ->writeUInt32LE(16000)
            ->writeUInt16LE(1)
            ->writeUInt16LE(16)
            ->writeUInt16LE(0);
        $streamPropertiesPayload = new StringWriter();
        $streamPropertiesPayload->writeGuid(StreamProperties::AUDIO_MEDIA)
            ->writeGuid(StreamProperties::NO_ERROR_CORRECTION)
            ->writeInt64LE(0)
            ->writeUInt32LE($typeSpecific->getSize())
            ->writeUInt32LE(0)
            ->writeUInt16LE(0x0001)
            ->writeUInt32LE(0)
            ->write($typeSpecific->toString());
        $streamProperties = $this->wrapObject(BaseObject::STREAM_PROPERTIES, $streamPropertiesPayload->toString());

        $indexParametersPayload = new StringWriter();
        $indexParametersPayload->writeUInt32LE(5000)
            ->writeUInt16LE(1)
            ->writeUInt16LE(1)
            ->writeUInt16LE(3);
        $indexParameters = $this->wrapObject(BaseObject::INDEX_PARAMETERS, $indexParametersPayload->toString());

        $headerExtensionObjects = new StringWriter();
        $headerExtensionObjects->write($indexParameters);

        $headerExtensionPayload = new StringWriter();
        $headerExtensionPayload->writeGuid($fileId)
            ->writeUInt16LE(0)
            ->writeUInt32LE($headerExtensionObjects->getSize())
            ->write($headerExtensionObjects->toString());
        $headerExtension = $this->wrapObject(BaseObject::HEADER_EXTENSION, $headerExtensionPayload->toString());

        $headerPayload = new StringWriter();
        $headerPayload->writeUInt32LE(3)
            ->writeInt8(1)
            ->writeInt8(2)
            ->write($fileProperties)
            ->write($streamProperties)
            ->write($headerExtension);
        $header = $this->wrapObject(BaseObject::HEADER, $headerPayload->toString());

        $dataPayload = new StringWriter();
        $dataPayload->writeGuid($fileId)
            ->writeInt64LE(0)
            ->writeUInt16LE(0);
        $data = $this->wrapObject(BaseObject::DATA, $dataPayload->toString());

        $file = $header . $data;

        return $this->patchFileSize($file, strlen($file));
    }

    private function wrapObject(string $identifier, string $payload): string
    {
        $writer = new StringWriter();
        $writer->writeGuid($identifier)
            ->writeInt64LE(16 + 8 + strlen($payload))
            ->write($payload);

        return $writer->toString();
    }

    private function patchFileSize(string $file, int $fileSize): string
    {
        $fileSizeOffset = 70; // computed offset of file size field within minimal ASF layout
        $fileSizeBinary = pack('V2', $fileSize & 0xffffffff, ($fileSize >> 32) & 0xffffffff);

        return substr_replace($file, $fileSizeBinary, $fileSizeOffset, 8);
    }
}
