<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\HTTP\Request;
use App\Core\HTTP\Response;
use App\Core\Renderer\RendererInterface;
use App\Core\Uploader\Uploader;
use App\Core\Validator\Validator;
use App\Entities\Ingredient;
use App\Entities\Recipe;
use App\Entities\RecipeIngredient;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class RecipeController extends BaseController
{
    const IMAGE_PATH = ROOT.'/public/uploads/recipes/';


    private Recipe $recipe;
    private Ingredient $ingredient;


    public function __construct()
    {
        parent::__construct();
        $this->recipe = new Recipe();
        $this->ingredient = new Ingredient();
    }


    public function getRecipeValidator($values): Validator
    {
        $validator = new Validator($values);

        return $validator->isRequired('label', 'people', 'preparationTime', 'cookingTime')
            ->length('label', 1, 255)
            ->isInt('people')
            ->isDateTime('preparationTime', 'H:i')
            ->isDateTime('cookingTime', 'H:i');
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
                'recipes/index',
                ['recipes' => $this->recipe->getAllByUser($this->user)]
            );
        }
    }


    /**
     * @return Response|RendererInterface
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function showRecipe(int $id)
    {
        $recipe = $this->recipe->find($id);

        if (!$this->isGranted('ROLE_ADMIN') || $this->user != $recipe->getUser()) {
            $response = new Response();
            return $response->redirectToLogin();
        } else {
            return $this->renderer->render(
                'recipes/show',
                ['recipe' => $recipe]
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
            return $this->renderer->render('recipes/create');
        }
    }


    public function add(): Response
    {
        $response = new Response();
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $response->jsonResponse(self::NOT_GRANTED_MESSAGE, Response::ERROR_STATUS);
        }

        $request = new Request();
        $recipe = $request->getPostData();
        $validator = $this->getRecipeValidator($recipe);
        if (!$validator->isValid()) {
            return $response->jsonResponse($validator->getErrors(), Response::ERROR_STATUS);
        }

        $newRecipe = new Recipe();
        $newRecipe->setLabel($recipe['label']);
        $newRecipe->setDescription($recipe['description']);
        $newRecipe->setPeople($recipe['people']);
        $newRecipe->setPreparationTime($recipe['preparationTime']);
        $newRecipe->setCookingTime($recipe['cookingTime']);
        $newRecipe->setUser($this->user);

        $uploader = new Uploader();
        if ($uploader->isValidImage($request->getFilesData('image'))) {
            $file = $uploader->saveFile($request->getFilesData('image'), $recipe['label'], self::IMAGE_PATH);
            $newRecipe->setImage($file);
        } else {
            return $response->jsonResponse($uploader::ERROR_MESSAGE, Response::ERROR_STATUS);
        }

        $newRecipe->setId($newRecipe->save());
        return $response->jsonResponse('/recipes/'.$newRecipe->getId().'/add-ingredient', Response::SUCCESS_STATUS);
    }


    /**
     * @return Response|RendererInterface
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function showAddIngredient(int $id)
    {
        $recipe = $this->recipe->find($id);

        if (!$this->isGranted('ROLE_ADMIN') || $this->user != $recipe->getUser()) {
            $response = new Response();
            return $response->redirectToLogin();
        } else {
            return $this->renderer->render(
                'recipes/add-ingredient',
                ['recipe' => $recipe, 'ingredients' => $this->ingredient->findAllNotSelected($recipe)]
            );
        }
    }


    public function addIngredient(int $id): Response
    {
        $recipe = $this->recipe->find($id);

        $response = new Response();
        if (!$this->isGranted('ROLE_ADMIN') || $this->user != $recipe->getUser()) {
            return $response->jsonResponse(self::NOT_GRANTED_MESSAGE, Response::ERROR_STATUS);
        } else {
            $request = new Request();
            $data = $request->getPostData();

            $recipeIngredient = new RecipeIngredient();
            $recipeIngredient->setIdRecipe($recipe->getId());
            $recipeIngredient->setIngredient($this->ingredient->find($data['ingredient']));
            $recipeIngredient->setquantity($data['quantity']);
            $recipeIngredient->save();

            return $response->jsonResponse('/recipes/'.$recipe->getId(), Response::SUCCESS_STATUS);
        }
    }


    public function removeIngredient(int $id, int $ingredient): Response
    {
        $recipe = $this->recipe->find($id);

        if (!$this->isGranted('ROLE_ADMIN') || $this->user != $recipe->getUser()) {
            $response = new Response();
            return $response->redirectToLogin();
        }

        $recipeIngredient = new RecipeIngredient();
        $recipeIngredient = $recipeIngredient->findBy($recipe->getId(), $ingredient);

        $recipeIngredient->remove();

        $response = new Response();
        return $response->redirect(BASE_URL.'/recipes/'.$id);
    }


    /**
     * @return Response|RendererInterface
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function showAddInstruction(int $id)
    {
        $recipe = $this->recipe->find($id);

        if (!$this->isGranted('ROLE_ADMIN') || $this->user != $recipe->getUser()) {
            $response = new Response();
            return $response->redirectToLogin();
        } else {
            return $this->renderer->render(
                'recipes/add-instruction',
                ['recipe' => $recipe]
            );
        }
    }


    public function addInstruction(int $id): Response
    {
        $recipe = $this->recipe->find($id);

        $response = new Response();
        if (!$this->isGranted('ROLE_ADMIN') || $this->user != $recipe->getUser()) {
            return $response->jsonResponse(self::NOT_GRANTED_MESSAGE, Response::ERROR_STATUS);
        } else {
            $request = new Request();
            $data = $request->getPostData();

            $recipe->addInstruction([$data['instruction']]);
            $recipe->save();

            return $response->jsonResponse('/recipes/'.$recipe->getId(), Response::SUCCESS_STATUS);
        }
    }


    public function delete(int $id): Response
    {
        $recipe = $this->recipe->find($id);

        $response = new Response();
        if (!$this->isGranted('ROLE_ADMIN') || $this->user != $recipe->getUser()) {
            return $response->jsonResponse(self::NOT_GRANTED_MESSAGE, Response::ERROR_STATUS);
        }

        $recipe->remove();

        return $response->redirect(BASE_URL.'/recipes/');
    }
}