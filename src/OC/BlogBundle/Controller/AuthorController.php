<?php

namespace OC\BlogBundle\Controller;


use OC\BlogBundle\Entity\Author;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use OC\BlogBundle\Exception\ResourceValidationException;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorController extends FOSRestController
{

    const VLD_MSG_TEMPLATE = 'The JSON sent contains invalid data. Here are the errors you need to correct: ';
    const VLD_MSG_TEMPLATE_PARAM = 'Field %s: %s';

    /**
     * @Rest\Put(
     *     path = "/authors/{id}",
     *     name = "app_author_update",
     *     requirements = {"id"="\d+"}
     * )
     * @Rest\View(StatusCode = Response::HTTP_OK)
     * @ParamConverter("authorJson", converter="fos_rest.request_body")
     * @ParamConverter("authorDB", converter="doctrine.orm")
     */
    public function updateAction(Author $authorJson, Author $authorDB, ConstraintViolationList $violations)
    {
        // if author id does not exist, the ParamConverter annotation throws a 404 exception

        // check constraint violations
        $this->checkViolations($violations);

        $em = $this->getDoctrine()->getManager();

        // update each field
//        $authorDB->setTitle($authorJson->getTitle());
//        $authorDB->setContent($authorJson->getContent());

        $authorDB->replaceFrom($authorJson);

        $em->flush();

        // returns the updated author
        return $authorDB;
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