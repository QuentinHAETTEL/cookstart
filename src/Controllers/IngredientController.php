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


    /**
     * @return Response|RendererInterface
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function showAdd()
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $response = new Response();
            return $response->redirectToLogin();
        } else {
            return $this->renderer->render(
                'ingredients/create',
                ['units' => $this->unit->getAll()]
            );
        }
    }


    public function add(): Response
    {
        $response = new Response();
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $response->jsonResponse(self::NOT_GRANTED_MESSAGE, Response::ERROR_STATUS);
        }

        $request = new Request();
        $ingredient = $request->getPostData();
        $validator = $this->getIngredientValidator($ingredient);
        if (!$validator->isValid()) {
            return $response->jsonResponse($validator->getErrors(), Response::ERROR_STATUS);
        }

        $unit = new Unit();
        $unit->setId($ingredient['unit']);
        $this->ingredient->setLabel($ingredient['label']);
        $this->ingredient->setUnit($unit);

        $uploader = new Uploader();
        if ($uploader->isValidImage($request->getFilesData('image'))) {
            $file = $uploader->saveFile($request->getFilesData('image'), $ingredient['label'], self::IMAGE_PATH);
            $this->ingredient->setImage($file);
        } else {
            return $response->jsonResponse($uploader::ERROR_MESSAGE, Response::ERROR_STATUS);
        }

        $this->ingredient->save();
        return $response->jsonResponse('/ingredients', Response::SUCCESS_STATUS);
    }
}
