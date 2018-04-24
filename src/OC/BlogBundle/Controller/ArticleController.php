<?php

namespace OC\BlogBundle\Controller;

use OC\BlogBundle\Entity\Article;
use OC\BlogBundle\Entity\Author;
use OC\BlogBundle\Exception\ResourceValidationException;
use OC\BlogBundle\Representation\Articles;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ArticleController extends FOSRestController
{
    const VLD_MSG_TEMPLATE = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
    const VLD_MSG_TEMPLATE_PARAM = 'Field %s: %s';

    /**
     * @Rest\Get("/articles", name="app_article_list")
     * @Rest\QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]",
     *     nullable=true,
     *     description="The keyword to search for."
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="Sort order (asc or desc)"
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="15",
     *     description="Max number of movies per page."
     * )
     * @Rest\QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     default="0",
     *     description="The pagination offset"
     * )
     * @Rest\View()
     */
    public function listAction(ParamFetcherInterface $paramFetcher)
    {
        $pager = $this->getDoctrine()->getRepository('OCBlogBundle:Article')->search(
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('offset')
        );

        return new Articles($pager);
    }

    /**
     * @Rest\Get(
     *     path = "/articles/{id}",
     *     name = "app_article_show",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View
     */
    public function showAction(Article $article)
    {
        return $article;
    }

    /**
     * @Rest\Post("/articles")
     * @Rest\View(StatusCode = Response::HTTP_CREATED)
     * @ParamConverter("article", converter="fos_rest.request_body")
     */
    public function createAction(Article $article, ConstraintViolationList $violations)
    {
        $this->checkViolations($violations);

        $em = $this->getDoctrine()->getManager();

        $em->persist($article);

        $em->flush();

        return $article;
    }

    /**
     * @Rest\Put(
     *     path = "/articles/{id}",
     *     name = "app_article_update",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = Response::HTTP_OK)
     * @ParamConverter("articleJson", converter="fos_rest.request_body")
     * @ParamConverter("articleDB", converter="doctrine.orm")
     */
    public function updateAction(Article $articleJson, Article $articleDB, ConstraintViolationList $violations)
    {
        // if article id does not exist, the ParamConverter annotation throws a 404 exception

        // check constraint violations
        $this->checkViolations($violations);

        $em = $this->getDoctrine()->getManager();

        // update each field
//        $articleDB->setTitle($articleJson->getTitle());
//        $articleDB->setContent($articleJson->getContent());

        $articleDB->replaceFrom($articleJson);

        $em->flush();

        // returns the updated article
        return $articleDB;
    }

    /**
     * @Rest\Delete(
     *     path = "/articles/{id}",
     *     name = "app_article_delete",
     *     requirements={"id"="\d+"}
     * )
     * @ParamConverter("article", converter="doctrine.orm")
     * @Rest\View(StatusCode = Response::HTTP_OK)
     */
    public function deleteAction(Article $article)
    {
        // if article id does not exist, the ParamConverter annotation throws a 404 exception

        $em = $this->getDoctrine()->getManager();

        $em->remove($article);

        $em->flush();
    }

    /**
     * Checks a list of violations. If list is not empty, throws a ResourceValidationException.
     *
     * @TODO move this method in a helper class. Might be useful in another controller which uses a ConstraintViolationList (ex: future AuthorController). Do not forget to move the two const !
     *
     * @param ConstraintViolationListInterface $violations      A list of ConstraintViolation
     * @param string $msgTemplate                               The error message template
     * @param string $msgParam                                  The error message parameters (%s: ConstraintViolation.getPropertyPath, %s: ConstraintViolation.getMessage)
     * @throws ResourceValidationException
     */
    protected function checkViolations(ConstraintViolationListInterface $violations, $msgTemplate = ArticleController::VLD_MSG_TEMPLATE, $msgParam = ArticleController::VLD_MSG_TEMPLATE_PARAM)
    {
        if(count($violations) > 0) {
            $message = $msgTemplate;
            foreach($violations as $violation) {
                $message .= sprintf($msgParam, $violation->getPropertyPath(), $violation->getMessage());
            }
            throw new ResourceValidationException($message);
        }
    }
}
