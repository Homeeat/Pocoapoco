<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author    	Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see			https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license  	https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\Aws;

use Ntch\Pocoapoco\Aws\Base as AwsBase;
use Aws\S3\S3Client;
use Exception;

class Aws extends AwsBase
{

    /**
     * @var string
     */
    protected string $awsName;

    /**
     * @var array
     */
    protected array $aws = [];

    /**
     * Construct
     *
     * @param string $awsName
     *
     * @return void
     */
    public function __construct(string $awsName)
    {
        // mail config
        $this->awsName = $awsName;
        $this->aws[$awsName] = new S3Client(
            [
                'version' => self::$awsList[$awsName]['version'],
                'region' => self::$awsList[$awsName]['region'],
                'credentials' => [
                    'key' => self::$awsList[$awsName]['key'],
                    'secret' => self::$awsList[$awsName]['secret'],
                ],
            ]
        );
    }

    //-----------------------------------------------------
    // S3
    //-----------------------------------------------------

    /**
     * Aws s3 read file content.
     *
     * @param string $bucket
     * @param string $awsPath
     * @param string $awsFile
     * @param array $sseKey
     *
     * @return array
     */
    public function s3_read(string $bucket, string $awsPath, string $awsFile, array $sseKey = []): array
    {
        $data['action'] = 'READ';

        if ($awsPath == '/') {
            $key = $awsFile;
        } else {
            $awsPath = trim($awsPath, '/');
            $key = "$awsPath/$awsFile";
        }

        $awsParam = [
            'Bucket' => $bucket,
            'Key' => $key
        ];

        // SSE
        $sse = $this->sse_decode($sseKey);
        if (!empty($sse)) {
            $sse = $this->sse_decode($sseKey);

            $awsParam['SSECustomerAlgorithm'] = 'AES256';
            $awsParam['SSECustomerKey'] = $sse['key'];
            $awsParam['SSECustomerKeyMD5'] = $sse['md5'];
        }

        $aws = $this->aws[$this->awsName]->getObject($awsParam);

        if ($aws['@metadata']['statusCode'] == 200) {
            $data['status'] = 'SUCCESS';
            $data['url'] = $aws['@metadata']['effectiveUri'];
            $data['result'] = (string)$aws['Body'];
        } else {
            $data['status'] = 'ERROR';
        }

        return $data;
    }

    /**
     * Aws s3 download file.
     *
     * @param string $bucket
     * @param string $awsPath
     * @param string $awsFile
     * @param string $localPath
     * @param string $localFile
     * @param array $sseKey
     *
     * @return array
     */
    public function s3_download(string $bucket, string $awsPath, string $awsFile, string $localPath, string $localFile, array $sseKey = []): array
    {
        $data['action'] = 'DOWNLOAD';

        if (is_dir($localPath)) {
            if ($awsPath == '/') {
                $sourceFile = $awsFile;
            } else {
                $awsPath = trim($awsPath, '/');
                $sourceFile = "$awsPath/$awsFile";
            }

            $awsParam = [
                'Bucket' => $bucket,
                'Key' => $sourceFile,
                'SaveAs' => "$localPath/$localFile"
            ];

            // SSE
            $sse = $this->sse_decode($sseKey);
            if (!empty($sse)) {
                $sse = $this->sse_decode($sseKey);

                $awsParam['SSECustomerAlgorithm'] = 'AES256';
                $awsParam['SSECustomerKey'] = $sse['key'];
                $awsParam['SSECustomerKeyMD5'] = $sse['md5'];
            }

            $aws = $this->aws[$this->awsName]->getObject($awsParam);

            if ($aws['@metadata']['statusCode'] == 200) {
                $data['status'] = 'SUCCESS';
                $data['url'] = $aws['@metadata']['effectiveUri'];
            } else {
                $data['status'] = 'ERROR';
            }

        } else {
            $data['status'] = 'ERROR';
            $data['result'] = 'Path is not exist.';
        }

        return $data;
    }

    /**
     * Aws s3 upload file.
     * 【 Param 】
     *    security => 1：public, 2：private, 3：SSE encryption
     *
     * @param string $bucket
     * @param string $awsPath
     * @param string $awsFile
     * @param string $localPath
     * @param string $localFile
     * @param int $security
     *
     * @return array
     * @throws Exception
     */
    public function s3_upload(string $bucket, string $awsPath, string $awsFile, string $localPath, string $localFile, int $security): array
    {
        $data['action'] = 'UPLOAD';

        if (is_dir($localPath)) {

            $sourceFile = "$localPath/$localFile";
            if (is_file($sourceFile)) {

                if ($awsPath == '/') {
                    $key = $awsFile;
                } else {
                    $awsPath = trim($awsPath, '/');
                    $key = "$awsPath/$awsFile";
                }

                $awsParam = [
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'SourceFile' => $sourceFile
                ];

                switch ($security) {
                    case 1:
                        $awsParam['ACL'] = 'public-read';
                        break;
                    case 2:
                        $awsParam['ACL'] = 'private';
                        break;
                    case 3:
                        $sse = $this->sse_get();

                        $awsParam['SSECustomerAlgorithm'] = 'AES256';
                        $awsParam['SSECustomerKey'] = $sse['key'];
                        $awsParam['SSECustomerKeyMD5'] = $sse['md5'];
                        break;
                }

                $aws = $this->aws[$this->awsName]->putObject($awsParam);

                if ($aws['@metadata']['statusCode'] == 200) {
                    $data['status'] = 'SUCCESS';
                    $data['url'] = $aws['@metadata']['effectiveUri'];
                    if ($security === 3) {
                        $data['sse']['key'] = base64_encode($sse['key']);
                        $data['sse']['md5'] = base64_encode($sse['md5']);
                    }
                } else {
                    $data['status'] = 'ERROR';
                }
            } else {
                $data['status'] = 'ERROR';
                $data['result'] = 'File is not exist.';
            }
        } else {
            $data['status'] = 'ERROR';
            $data['result'] = 'Path is not exist.';
        }

        return $data;
    }

    /**
     * Aws s3 copy bucket file to another bucket.
     *
     * @param string $sourceBucket
     * @param string $sourcePath
     * @param string $sourceFile
     * @param string $targetBucket
     * @param string $targetPath
     * @param string $targetFile
     *
     * @return array
     */
    public function s3_copy(string $sourceBucket, string $sourcePath, string $sourceFile, string $targetBucket, string $targetPath, string $targetFile): array
    {
        $data['action'] = 'COPY';

        if ($targetPath == '/') {
            $target = $targetFile;
        } else {
            $targetPath = trim($targetPath, '/');
            $target = "$targetPath/$targetFile";
        }

        if ($sourcePath == '/') {
            $source = "$sourceBucket/$sourceFile";
        } else {
            $sourcePath = trim($sourcePath, '/');
            $source = "$sourceBucket/$sourcePath/$sourceFile";
        }

        $awsParam = [
            'Bucket' => $targetBucket,
            'Key' => $target,
            'CopySource' => $source
        ];
        $aws = $this->aws[$this->awsName]->copyObject($awsParam);

        if ($aws['@metadata']['statusCode'] == 200) {
            $data['status'] = 'SUCCESS';
            $data['url'] = $aws['@metadata']['effectiveUri'];
        } else {
            $data['status'] = 'ERROR';
        }

        return $data;
    }

    //-----------------------------------------------------
    // SSE
    //-----------------------------------------------------

    /**
     * Aws sse get.
     *
     * @return array
     * @throws Exception
     */
    public function sse_get(): array
    {
        $keyParam = bin2hex(random_bytes(32));

        $sse['key'] = hash('sha256', $keyParam, true);
        $sse['md5'] = md5($sse['key'], true);

        return $sse;
    }

    /**
     * Aws sse decode.
     *
     * @param array $sseKey
     *
     * @return array
     */
    public function sse_decode(array $sseKey): array
    {
        $sse = [];
        if (isset($sseKey['key']) && isset($sseKey['md5'])) {
            $sse['key'] = base64_decode($sseKey['key']);
            $sse['md5'] = base64_decode($sseKey['md5']);
        }

        return $sse;
    }

}