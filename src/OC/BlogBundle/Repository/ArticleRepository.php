<?php

namespace OC\BlogBundle\Repository;

/**
 * ArticleRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArticleRepository extends AbstractRepository
{
    /**
     * @param string $term Term to search for
     * @param string $order Search order (asc|desc) default=asc
     */
    public function search($term, $order = 'asc', $limit = 20, $offset = 0)
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->orderBy('a.title', $order)
        ;

        if($term) {
            $qb
                ->where('a.title LIKE ?1')
                ->setParameter(1, '%' . $term . '%')
            ;
        }

        return $this->paginate($qb, $limit, $offset);
    }
}