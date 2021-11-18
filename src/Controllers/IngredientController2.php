<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\HTTP\Request;
use App\Core\HTTP\Response;
use App\Core\Renderer\RendererInterface;
use App\Core\Uploader\Uploader;
use App\Core\Validator\Validator;
use App\Entities\Ingredient;
use App\Entities\Unit;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class IngredientController extends BaseController
{
    const IMAGE_PATH = ROOT.'/public/uploads/ingredients/';


    private Ingredient $ingredient;
    private Unit $unit;


    public function __construct()
    {
        parent::__construct();
        $this->ingredient = new Ingredient();
        $this->unit = new Unit();
    }


    public function getIngredientValidator($values): Validator
    {
        $validator = new Validator($values);

        return $validator->isRequired('label', 'unit')
            ->length('label', 1, 255)
            ->length('unit', 1, 11);
    }


    /**
     * @return Response|RendererInterface
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function index()
    {
        if (!$this->isGranted('ROLE_USER')) {
            $response = new Response();
            return $response->redirectToLogin();
        } else {
            return $this->renderer->render(
                'ingredients/index',
                ['ingredients' => $this->ingredient->getAll()]
            );
        }
    }
}
