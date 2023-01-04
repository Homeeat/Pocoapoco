<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author        Roy Lee <royhylee@mail.npac-ntch.org>
 *
 * @see            https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license    https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
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
     * @param string $mvc
     */
    public function __construct(string $awsName, string $mvc)
    {
        // mail config
        $this->awsName = $awsName;
        $this->aws[$awsName] = new S3Client(
            [
                'version' => self::$awsList[$mvc][$awsName]['version'],
                'region' => self::$awsList[$mvc][$awsName]['region'],
                'credentials' => [
                    'key' => self::$awsList[$mvc][$awsName]['key'],
                    'secret' => self::$awsList[$mvc][$awsName]['secret'],
                ],
            ]
        );
    }

    //-----------------------------------------------------
    // S3
    //-----------------------------------------------------

    /**
     * Aws s3 file is exist.
     *
     * @param string $bucket
     * @param string $awsPath
     * @param string $awsFile
     * @param array $sseKey
     *
     * @return bool
     */
    public function s3_exist(string $bucket, string $awsPath, string $awsFile, array $sseKey = []): bool
    {
        if ($awsPath == '/') {
            $key = $awsFile;
        } else {
            $awsPath = trim($awsPath, '/');
            $key = "$awsPath/$awsFile";
        }

        // SSE
        $seeParam = [];
        $sse = $this->sse_decode($sseKey);
        if (!empty($sse)) {
            $sse = $this->sse_decode($sseKey);

            $seeParam['SSECustomerAlgorithm'] = 'AES256';
            $seeParam['SSECustomerKey'] = $sse['key'];
            $seeParam['SSECustomerKeyMD5'] = $sse['md5'];
        }

        return $this->aws[$this->awsName]->doesObjectExist($bucket, $key, $seeParam);
    }

    /**
     * Aws s3 bucket file list.
     *
     * @param string $bucket
     *
     * @return array
     */
    public function s3_list(string $bucket)
    {
        $data['action'] = 'LIST';

        $awsParam = [
            'Bucket' => $bucket
        ];

        $request = $this->aws[$this->awsName]->listObjects($awsParam);
        $obj = $request->search('Contents[].Key');

        $data['result'] = [];
        if (!is_null($obj)) {
            foreach ($request->search('Contents[].Key') as $key) {
                array_push($data['result'], $key);
            }
        }

        return $data;
    }

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

        // exist
        $fileExist = $this->s3_exist($bucket, $awsPath, $awsFile, $sseKey);

        if ($fileExist) {

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

            $request = $this->aws[$this->awsName]->getObject($awsParam);

            if ($request['@metadata']['statusCode'] == 200) {
                $data['status'] = 'SUCCESS';
                $data['url'] = $request['@metadata']['effectiveUri'];
                $data['result'] = (string)$request['Body'];
            } else {
                $data['status'] = 'ERROR';
            }
        } else {
            $data['status'] = 'ERROR';
            $data['result'] = 'File is not exist';
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

            // exist
            $fileExist = $this->s3_exist($bucket, $awsPath, $awsFile, $sseKey);

            if ($fileExist) {

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

                $request = $this->aws[$this->awsName]->getObject($awsParam);

                if ($request['@metadata']['statusCode'] == 200) {
                    $data['status'] = 'SUCCESS';
                    $data['url'] = $request['@metadata']['effectiveUri'];
                } else {
                    $data['status'] = 'ERROR';
                }
            } else {
                $data['status'] = 'ERROR';
                $data['result'] = 'File is not exist';
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
     * @param int $download
     *
     * @return array
     * @throws Exception
     */
    public function s3_upload(string $bucket, string $awsPath, string $awsFile, string $localPath, string $localFile, int $security, int $download = 0): array
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

                // download
                if ((bool)$download) {
                    $awsParam['ContentType'] = 'binary/octet-stream';
                }

                $request = $this->aws[$this->awsName]->putObject($awsParam);

                if ($request['@metadata']['statusCode'] == 200) {
                    $data['status'] = 'SUCCESS';
                    $data['url'] = $request['@metadata']['effectiveUri'];
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
     * @return array
     */
    public function s3_copy(string $sourceBucket, string $sourcePath, string $sourceFile, string $targetBucket, string $targetPath, string $targetFile): array
    {
        $data['action'] = 'COPY';

        // exist
        $fileExist = $this->s3_exist($sourceBucket, $sourcePath, $sourceFile);

        if ($fileExist) {

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

            $request = $this->aws[$this->awsName]->copyObject($awsParam);

            if ($request['@metadata']['statusCode'] == 200) {
                $data['status'] = 'SUCCESS';
                $data['url'] = $request['@metadata']['effectiveUri'];
            } else {
                $data['status'] = 'ERROR';
            }

        } else {
            $data['status'] = 'ERROR';
            $data['result'] = 'File is not exist';
        }

        return $data;
    }

    /**
     * Aws s3 delete file.
     *
     * @param string $bucket
     * @param string $awsPath
     * @param string $awsFile
     * @param array $sseKey
     *
     * @return array
     */
    public function s3_delete(string $bucket, string $awsPath, string $awsFile, array $sseKey = []): array
    {
        $data['action'] = 'DELETE';

        // exist
        $fileExist = $this->s3_exist($bucket, $awsPath, $awsFile, $sseKey);

        if ($fileExist) {

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

            $request = $this->aws[$this->awsName]->deleteObject($awsParam);

            if ($request['@metadata']['statusCode'] == 204) {
                $data['status'] = 'SUCCESS';
            } else {
                $data['status'] = 'ERROR';
            }
        } else {
            $data['status'] = 'ERROR';
            $data['result'] = 'File is not exist';
        }

        return $data;
    }


    /**
     * Aws s3 get file url.
     *
     * @param string $bucket
     * @param string $awsPath
     * @param string $awsFile
     * @param int $effectTime
     * @param array $sseKey
     *
     * @return array
     */
    public function s3_get(string $bucket, string $awsPath, string $awsFile, int $effectTime, array $sseKey = []): array
    {
        $data['action'] = 'GET';

        // exist
        $fileExist = $this->s3_exist($bucket, $awsPath, $awsFile, $sseKey);

        if ($fileExist) {

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

            $aws = $this->aws[$this->awsName]->getCommand('GetObject', $awsParam);

            if ($effectTime === -1) {

                $awsParam['CopySource'] = "$bucket/$key";
                $awsParam['ACL'] = 'public-read';
                $awsParam['MetadataDirective'] = 'REPLACE';

                $headers = $this->aws[$this->awsName]->HeadObject($awsParam);
                $awsParam['ContentType'] = $headers['ContentType'];

                $request = $this->aws[$this->awsName]->copyObject($awsParam);
                $url = $request->get('ObjectURL');

            } else {

                $request = $this->aws[$this->awsName]->createPresignedRequest($aws, "+$effectTime minutes");
                $url = (string)$request->getUri();

            }

            $request2 = $this->aws[$this->awsName]->getObject($awsParam);
            $result = (string)$request2['Body'];

            $data['status'] = 'SUCCESS';
            $data['url'] = $url;

        } else {
            $data['status'] = 'ERROR';
            $result = 'File is not exist';
        }

        $data['result'] = $result;

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