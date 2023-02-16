<?php

/**
 * Pocoapoco - PHP framework.
 *
 * @author      Roy Lee <royhylee@mail.npac-ntch.org>
 * @see         https://github.com/Homeeat/Pocoapoco  - GitHub project
 * @license     https://github.com/Homeeat/Pocoapoco/blob/main/LICENSE  - MIT LICENSE
 */

namespace Ntch\Pocoapoco\WebRestful;

use Ntch\Pocoapoco\Psr\Psr4\AutoloaderClass;
use Ntch\Pocoapoco\Error\Base as ErrorBase;
use Ntch\Pocoapoco\Http\Server\Nginx;
use Ntch\Pocoapoco\Http\Request\Globals;
use Ntch\Pocoapoco\Http\Request\Header;
use Ntch\Pocoapoco\Http\Request\Body;
use phpDocumentor\Reflection\Type;

class WebRestful
{
    use \Ntch\Pocoapoco\Tools\Uuid;

    /**
     * @var AutoloaderClass
     */
    protected $psr4;

    /**
     * @var Nginx
     */
    protected $nginx;

    /**
     * /**
     * @var Globals
     */
    protected $globals;

    /**
     * @var Header
     */
    protected $header;

    /**
     * @var Body
     */
    protected $body;

    /**
     * @var string
     */
    private string $class_name;

    /**
     * @var array
     */
    protected array $nginxPath = [];

    /**
     * @var array
     */
    protected array $uri = [];

    /**
     * @staticvar array
     */
    public static array $uriVariable = [];

    /**
     * @var string
     */
    protected string $basePath;

    /**
     * @var string
     */
    protected string $absolutePath;

    /**
     * @var string
     */
    protected string $absoluteFile;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->psr4 = new AutoloaderClass();

        $nginx = new Nginx();
        $nginx->setNginx();
        $this->nginx = $nginx->getNginx();

        $globals = new Globals();
        $globals->setGlobals();
        $this->globals = $globals->getGlobals();

        $header = new Header();
        $header->setHeaders();
        $this->header = $header->getHeader();

        $body = new Body();
        $body->setBody();
        $this->body = $body->getBody();
    }

    /**
     * Set Uri variable.
     *
     * @return void
     */
    protected function setUriVariable()
    {
        for ($i = 1; $i <= count($this->nginxPath); $i++) {
            if (str_starts_with($this->uri[$i], ':')) {
                $uriParameter = substr($this->uri[$i], 1);
                $nginxPathValue = $this->nginxPath[$i];
                self::$uriVariable[$uriParameter] = $nginxPathValue;
            }
        }
    }

    /**
     * Set base path.
     *
     * @param string $baseFolder
     *
     * @return void
     */
    protected function setBasePath(string $baseFolder)
    {
        $this->basePath = $this->nginx->conf['project_root'] . $baseFolder;
    }

    /**
     * Set absolute path.
     *
     * @param string $folderName
     * @param string $path
     *
     * @return void
     */
    protected function setAbsolutePath(string $folderName, string $path)
    {
        if ($path !== '/') {
            $path .= '/';
        }
        $this->absolutePath = $this->nginx->conf['project_root'] . $folderName . $path;
    }

    /**
     * Set absolute file path.
     *
     * @param string $fileName
     * @param string $extension
     *
     * @return void
     */
    protected function setAbsoluteFile(string $fileName, string $extension)
    {
        $this->absoluteFile = $this->absolutePath . $fileName . '.' . $extension;
    }

    /**
     * Include file.
     *
     * @return void
     */
    protected function includeFile()
    {
        require_once($this->absoluteFile);
    }

    /**
     * Autoloader file.
     *
     * @param string $prefix
     * @param string $base_dir
     *
     * @return void
     */
    protected function autoloaderFile(string $prefix, string $base_dir)
    {
        $this->psr4->addNamespace($prefix, $base_dir);
        $this->psr4->register();
    }

    /**
     *  Sorting out request uri and nginx uri variables.
     *
     * @param string $uri
     *
     * @return void
     */
    public function withUriMatch(string $uri)
    {
        $this->nginxPath = explode('/', $this->nginx->uri['path']);
        foreach ($this->nginxPath as $key => $value) {
            if (empty($value)) {
                unset($this->nginxPath[$key]);
            }
        }

        $this->uri = explode('/', $uri);
        foreach ($this->uri as $key => $value) {
            if (empty($value)) {
                unset($this->uri[$key]);
            }
        }
    }

    /**
     * Check Uri is Match.
     *
     * @return bool
     */
    public function checkUriMatch(): bool
    {
        if (count($this->nginxPath) != count($this->uri)) {
            return false;
        }

        for ($i = 1; $i <= count($this->nginxPath); $i++) {
            if (str_starts_with($this->uri[$i], ':')) {
                continue;
            } else {
                if ($this->nginxPath[$i] != $this->uri[$i]) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check path is exist.
     *
     * @param string $path
     *
     * @return void
     */
    protected function checkPathExist(string $path)
    {
        return is_dir($path) ? null : ErrorBase::triggerError("Folder does not exist. Please create the folder in the following path： $path", 4, 0);
    }

    /**
     * Check file is exist.
     *
     * @param string $folder_name
     * @param string $file
     *
     * @return bool
     */
    protected function checkFileExist(string $folder_name, string $file): bool
    {
        $fileName = $this->class_name;

        // setting
        if ($folder_name === 'settings') {
            $passFile = ['project'];

            foreach ($passFile as $name) {
                if ($fileName == $name) {
                    return is_file($file);
                } else {
                    return is_file($file) ? true : ErrorBase::triggerError("File \"$fileName\" does not exist. Please create the File in the following file： $this->absoluteFile", 4, 0);
                }
            }
        }

        // other
        return is_file($file) ? true : ErrorBase::triggerError("File \"$fileName\" does not exist. Please create the File in the following file： $this->absoluteFile", 4, 0);
    }

    /**
     * Check function is exist.
     *
     * @param string $class
     * @param string $method
     *
     * @return void
     */
    protected function checkMethodExist(string $class, string $method)
    {
        return method_exists($class, $method) ? null : ErrorBase::triggerError("Method \"$method\" does not exist. Please create the method in the following file： $this->absoluteFile", 4, 0);
    }

    /**
     * Create class.
     *
     * @param string $class
     *
     * @return object
     */
    protected function createClass(string $class) {
        return new $class();
    }

    /**
     * Check function is can execute.
     *
     * @param object $class
     * @param string $method
     *
     * @return void
     */
    protected function checkMethodCanExecute(object $class, string $method)
    {
        return is_callable([$class, $method]) ? null : ErrorBase::triggerError("Method \"$method\" Access modifiers is not available. Please check the Access modifiers in the following file： $this->absoluteFile", 4, 0);
    }

    /**
     * Check method config.
     *
     * @param string $mvc
     * @param string|null $uri
     * @param string|null $path
     * @param string|null $class
     * @param string|null $method
     *
     * @return bool|null|void|object
     */
    protected function webRestfulCheckList(string $mvc, ?string $uri, ?string $path, ?string $class, ?string $method)
    {
        is_null($class) ? null : $this->class_name = $class;

        switch ($mvc) {
            case 'router':
                $folder_name = 'routes';

                $this->setBasePath($folder_name);
                $this->checkPathExist($this->basePath);

                $this->setAbsolutePath($folder_name, '/');
                $this->setAbsoluteFile($class, 'php');
                $this->checkFileExist($folder_name, $this->absoluteFile);

                $this->includeFile();
                break;
            case 'controller':
                $folder_name = 'controllers';

                $this->setBasePath($folder_name);
                $this->checkPathExist($this->basePath);

                $this->withUriMatch($uri);
                $uriExist = $this->checkUriMatch();

                if ($uriExist) {
                    $this->setUriVariable();

                    $this->setAbsolutePath($folder_name, $path);
                    $this->checkPathExist($this->absolutePath);

                    $this->setAbsoluteFile($class, 'php');
                    $this->checkFileExist($folder_name, $this->absoluteFile);

                    $this->includeFile();

                    $this->checkMethodExist($class, $method);
                    $createClass = $this->createClass($class);
                    $this->checkMethodCanExecute($createClass, $method);
                    return $createClass;
                }
                break;
            case 'public':
            case 'view':
                $folder_name = $mvc;

                $this->setBasePath($folder_name);
                $this->checkPathExist($this->basePath);

                if (!is_null($uri)) {
                    $this->withUriMatch($uri);
                    $uriExist = $this->checkUriMatch();
                    if ($uriExist) {
                        $this->setUriVariable();
                    }
                }

                if (is_null($uri) || $uriExist) {
                    $this->setAbsolutePath($folder_name, $path);
                    $this->checkPathExist($this->absolutePath);

                    $nameExtension = explode('.', $class);
                    $extension = isset($nameExtension[1]) ? $nameExtension[1] : 'php';
                    $this->setAbsoluteFile($nameExtension[0], $extension);
                    return $this->checkFileExist($folder_name, $this->absoluteFile);
                }
                break;
            case 'setting':
                $folder_name = 'settings';

                $this->setBasePath($folder_name);
                $this->checkPathExist($this->basePath);

                $this->setAbsolutePath($folder_name, $path);
                $this->checkPathExist($this->absolutePath);

                $this->setAbsoluteFile($class, 'ini');
                return $this->checkFileExist($folder_name, $this->absoluteFile);
            case 'model':
                $folder_name = 'models';

                $this->setBasePath($folder_name);
                $this->checkPathExist($this->basePath);

                $this->setAbsolutePath($folder_name, $path);
                $this->checkPathExist($this->absolutePath);

                $this->setAbsoluteFile($class, 'php');
                $this->checkFileExist($folder_name, $this->absoluteFile);

                $this->includeFile();
                $createClass = $this->createClass($class);
                if (!is_null($method)) {
                    $this->checkMethodExist($class, $method);
                }
                return $createClass;
            case 'library':
                $folder_name = 'libraries';

                $this->setBasePath($folder_name);
                $this->checkPathExist($this->basePath);
                break;
            case 'service':
                $folder_name = 'services';

                $this->setBasePath($folder_name);
                $this->checkPathExist($this->basePath);

                $this->setAbsolutePath($folder_name, $path);
                $this->checkPathExist($this->absolutePath);
                break;
            case 'log':
                $folder_name = 'log';

                $this->setBasePath($folder_name);
                $this->checkPathExist($this->basePath);
                break;
            default:
                ErrorBase::triggerError("WebRestful does not handle this method.", 4, 0);
        }
    }

}