<?php

namespace App\Core\Renderer;

use App\Core\App;
use App\Core\Data\DataTransformer;
use App\Core\HTTP\Request;
use App\Core\Translator\Translator;
use App\Core\Translator\TranslatorException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\TwigFilter;

class TwigRenderer implements RendererInterface
{
    private Environment $twig;
    private ?array $translations = [];


    public function __construct(App $app, ?string $path = ROOT.'/templates/')
    {
        $parameters = [];
        if ($app->getEnv() === 'dev') {
            $parameters['debug'] = true;
        }

        $request = new Request();

        $loader = new FilesystemLoader($path);
        $this->twig = new Environment($loader, $parameters);
        $this->twig->addExtension(new DebugExtension());

        $app = [
            'name' => App::getInstance()->getConfig()->get('app_name'),
            'get' => $request->getGetData(),
            'post' => $request->getPostData(),
            'baseUrl' => BASE_URL
        ];
        $this->twig->addGlobal('app', $app);

        $this->twig->addFilter($this->addFilters('trans', [$this, 'translateFilter']));
    }


    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(string $template, array $parameters = []): self
    {
        $this->twig->display($template.'.twig', $parameters);
        return $this;
    }


    public function addGlobal(string $key, $value): void
    {
        $this->twig->addGlobal($key, $value);
    }


    public function addFilters(string $name, array $method): TwigFilter
    {
        return new TwigFilter($name, $method);
    }


    /**
     * @throws TranslatorException
     */
    public function translateFilter($key): string
    {
        if (empty($this->translations)) {
            $translator = new Translator();
            $this->translations = $translator->getTranslations();
        }

        if (preg_match('/\./', $key)) {
            $dataTransformer = new DataTransformer();
            return $dataTransformer->arrayValueByStringKeys('.', $key, $this->translations);
        }

        if (array_key_exists($key, $this->translations)) {
            return $this->translations[$key];
        } else {
            return $key;
        }
    }
}
