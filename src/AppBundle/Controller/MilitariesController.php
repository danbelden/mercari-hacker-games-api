<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Rank;
use DateTime;
use AppBundle\Entity\Military;
use AppBundle\Entity\MilitarySkill as MilitarySkillEntity;
use AppBundle\Entity\Skill;
use AppBundle\Request\MilitarySkill;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MilitariesController extends Controller
{
    /**
     * @SWG\Get(
     *   path="/militaries",
     *   summary="Get list of militaries",
     *   tags={"Military"},
     *   consumes={"application/json"},
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response="200",
     *     description="List returned"
     *   )
     * )
     * @Route("/militaries", name="militaries")
     * @Method({"GET"})
     */
    public function listAction(Request $request)
    {
        $militariesRepository = $this->get('doctrine.orm.entity_manager')
            ->getRepository(Military::class);

        assert($militariesRepository instanceof EntityRepository);
        $militaries = $militariesRepository->findAll();

        $data = ['militaries' => $militaries];

        $serializer = $this->container->get('jms_serializer');
        $content = $serializer->serialize($data, 'json');

        $jsonResponse = new JsonResponse();
        $jsonResponse->setContent($content);

        return $jsonResponse;
    }

    /**
     * @SWG\Get(
     *   path="/militaries/{military_id}/ranks",
     *   summary="List of military rank",
     *   tags={"Military"},
     *   consumes={"application/json"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="military_id to fetch ranks for",
     *     in="path",
     *     name="military_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Number of rows to fetch",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     description="Number of rows to skip",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Rank created/matched"
     *   ),
     *   @SWG\Response(
     *     response="400",
     *     description="Invalid request"
     *   )
     * )
     * @Route("/militaries/{military_id}/ranks", name="get_military_ranks")
     * @Method({"Get"})
     */
    public function getMilitaryRanksAction(Request $request)
    {
        $militaryId = $request->get('military_id');
        $limit = $request->get('limit', 50);
        $offset = $request->get('offset', 0);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $militaryRepository = $entityManager->getRepository(Military::class);
        assert($entityManager instanceof EntityManager);
        assert($militaryRepository instanceof EntityRepository);

        $militaryEntity = $militaryRepository->find($militaryId);
        if (!($militaryEntity instanceof Military)) {
            throw new InvalidArgumentException('Military id is invalid');
        }

        $rankRepository = $entityManager
            ->getRepository(Rank::class);
        assert($rankRepository instanceof EntityRepository);

        $criteria = ['military_id' => $militaryEntity->getId()];
        $ranks = $rankRepository->findBy($criteria, null, $limit, $offset);

        $serializer = $this->container->get('jms_serializer');

        $data = [
            'ranks' => $ranks
        ];
        $content = $serializer->serialize($data, 'json');

        $jsonResponse = new JsonResponse();
        $jsonResponse->setContent($content);

        return $jsonResponse;
    }

    /**
     * @SWG\Get(
     *   path="/militaries/{military_id}/skills",
     *   summary="List of military skill",
     *   tags={"Military"},
     *   consumes={"application/json"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="military_id to fetch skills for",
     *     in="path",
     *     name="military_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Number of rows to fetch",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="offset",
     *     in="query",
     *     description="Number of rows to skip",
     *     required=false,
     *     type="integer"
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Skill created/matched"
     *   ),
     *   @SWG\Response(
     *     response="400",
     *     description="Invalid request"
     *   )
     * )
     * @Route("/militaries/{military_id}/skills", name="get_military_skills")
     * @Method({"Get"})
     */
    public function getMilitarySkillsAction(Request $request)
    {
        $militaryId = $request->get('military_id');
        $limit = $request->get('limit', 50);
        $offset = $request->get('offset', 0);

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $militaryRepository = $entityManager->getRepository(Military::class);
        assert($entityManager instanceof EntityManager);
        assert($militaryRepository instanceof EntityRepository);

        $militaryEntity = $militaryRepository->find($militaryId);
        if (!($militaryEntity instanceof Military)) {
            throw new InvalidArgumentException('Military id is invalid');
        }

        $skillRepository = $this->get('doctrine.orm.entity_manager')
            ->getRepository(Skill::class);
        assert($skillRepository instanceof EntityRepository);

        $skills = $skillRepository->findByMilitaryId($militaryId, $limit, $offset);

        $serializer = $this->container->get('jms_serializer');

        $data = [
            'skills' => $skills
        ];
        $content = $serializer->serialize($data, 'json');

        $jsonResponse = new JsonResponse();
        $jsonResponse->setContent($content);

        return $jsonResponse;
    }

    /**
     * @SWG\Post(
     *   path="/militaries/{military_id}/skills",
     *   summary="Add a military skill",
     *   tags={"Military"},
     *   consumes={"application/json"},
     *   produces={"application/json"},
     *   @SWG\Parameter(
     *     description="military_id to add the skill to",
     *     in="path",
     *     name="military_id",
     *     required=true,
     *     type="integer"
     *   ),
     *   @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="MilitarySkill",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/MilitarySkill"),
     *   ),
     *   @SWG\Response(
     *     response="200",
     *     description="Skill created/matched"
     *   ),
     *   @SWG\Response(
     *     response="400",
     *     description="Invalid request"
     *   )
     * )
     * @Route("/militaries/{military_id}/skills", name="add_military_skill")
     * @Method({"Post"})
     */
    public function addMilitarySkillsAction(Request $request)
    {
        $militaryId = $request->get('military_id');

        $entityManager = $this->get('doctrine.orm.entity_manager');
        $militaryRepository = $entityManager->getRepository(Military::class);
        assert($entityManager instanceof EntityManager);
        assert($militaryRepository instanceof EntityRepository);

        $militaryEntity = $militaryRepository->find($militaryId);
        if (!($militaryEntity instanceof Military)) {
            throw new InvalidArgumentException('Military id is invalid');
        }

        $body = $request->getContent();
        $decodedBody = json_decode($body);

        if (!isset($decodedBody->name)) {
            throw new BadRequestHttpException('Skill name is not provided');
        }

        $skillRepository = $this->get('doctrine.orm.entity_manager')
            ->getRepository(Skill::class);
        assert($skillRepository instanceof EntityRepository);

        $skillName = strtolower($decodedBody->name);

        $serializer = $this->container->get('jms_serializer');

        $skillEntity = $skillRepository->findOneBy(['name' => $skillName]);
        if (!($skillEntity instanceof Skill)) {

            $skillEntity = new Skill();
            $skillEntity->setName($skillName)
                ->setCreated(new \DateTime());

            $entityManager->persist($skillEntity);
            $entityManager->flush();
        }

        $militarySkillRepository = $this->get('doctrine.orm.entity_manager')
            ->getRepository(MilitarySkillEntity::class);
        assert($militarySkillRepository instanceof EntityRepository);

        $militarySkillEntity = $militarySkillRepository->findOneBy(
            [
                'skill_id' => $skillEntity->getId(),
                'military_id' => $militaryEntity->getId()
            ]
        );

        if ($militarySkillEntity instanceof MilitarySkillEntity) {
            throw new BadRequestHttpException('Skill is already linked to this military');
        }

        $militarySkillEntity = new MilitarySkillEntity();
        $militarySkillEntity->setMilitaryId($militaryEntity->getId())
            ->setSkillId($skillEntity->getId())
            ->setCreated(new DateTime());

        $entityManager->persist($militarySkillEntity);
        $entityManager->flush();

        $data = [
            'skill' => $skillEntity,
            'militarySkill' => $militarySkillEntity
        ];
        $content = $serializer->serialize($data, 'json');

        $jsonResponse = new JsonResponse();
        $jsonResponse->setContent($content);

        return $jsonResponse;
    }
}
