<?php

namespace Puzzle\ConnectBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class UserRepository extends EntityRepository
{
    /**
     * CUstom fin by
     *
     * @param array $fields
     * @param array $joins
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @param bool $useCache
     * @return Collection
     */
    public function customFindBy(array $fields = null, array $joins = null, array $criteria = null, array $orderBy = null, int $limit = null, int $offset = null, bool $useCache = false){
        $query= self::getQuery($fields, $joins, $criteria, $orderBy, $limit, $offset, $useCache);
        return $query->getResult();
    }
    
    /**
     * Custom find one by
     *
     * @param array $fields
     * @param array $joins
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @param bool $useCache
     * @return ArrayCollection | NULL
     */
    public function customFindOneBy(array $fields = null, array $joins = null, array $criteria = null, array $orderBy = null, int $limit = null, int $offset = null, bool $useCache = false){
        $query = self::getQuery($fields, $joins, $criteria, $orderBy, $limit, $offset, $useCache);
        return count($query->getResult()) > 0 ? $query->getResult()[0] : null;
    }
    
    /**
     * Count by
     *
     * @param array $joins
     * @param array $criteria
     * @return boolean
     */
    public function countBy(array $joins = null, array $criteria = null) {
        $queryBuilder = $this->_em
                            ->createQueryBuilder()
                            ->select('COUNT (DISTINCT o.id)')
                            ->from($this->_entityName, 'o');
        
        if (count($joins) > 0) {
            foreach ($joins as $join => $alias) {
                $queryBuilder = $queryBuilder->innerJoin('o.'.$join, $alias);
            }
        }
        
        $parameters = [];
        if (count($criteria) > 0) {
            foreach ($criteria as $key => $criterion) {
                $predicates = count(explode('.', $criterion[0])) > 1 ? $criterion[0] : 'o.'.$criterion[0];
                $predicates .= count($criterion) == 3 ? ' '.$criterion[2]: " =";
                
                if ($criterion[1] !== null) {
                    $predicates .= " :". str_ireplace('.', '', $criterion[0]);
                    $parameters[str_ireplace('.', '', $criterion[0])] = $criterion[1];
                }
                
                if ($key == 0) {
                    $queryBuilder = $queryBuilder->where($predicates);
                }elseif ($key > 0 && count($criterion) < 4) {
                    $queryBuilder = $queryBuilder->andWhere($predicates);
                }else {
                    $queryBuilder = $queryBuilder->orWhere($predicates);
                }
            }
        }
        
        if (count($parameters) > 0) {
            $queryBuilder = $queryBuilder->setParameters($parameters);
        }
        
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
    
    
    /**
     * Get custom query
     *
     * @param array $fields
     * @param array $joins
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @param bool $useCache
     * @return array|mixed|\Doctrine\DBAL\Driver\Statement|NULL
     */
    public function getQuery(array $fields = null, array $joins = null, array $criteria = null, array $orderBy = null, int $limit = null, int $offset = null, bool $useCache = false) {
        if (count($fields) > 0) {
            foreach ($fields as $key => $field) {
                $fields[$key] = 'o.'.$field;
            }
        }else {
            $fields = ['o'];
        }
        
        $queryBuilder = $this->_em
                            ->createQueryBuilder()
                            ->select(implode(',', $fields))
                            ->from($this->_entityName, 'o');
        
        if (count($joins) > 0) {
            foreach ($joins as $join => $alias) {
                $queryBuilder = $queryBuilder->innerJoin('o.'.$join, $alias);
            }
        }
        
        $parameters = [];
        if (count($criteria) > 0) {
            foreach ($criteria as $key => $criterion) {
                $predicates = count(explode('.', $criterion[0])) > 1 ? $criterion[0] : 'o.'.$criterion[0];
                $predicates .= count($criterion) == 3 ? ' '.$criterion[2]: " =";
                
                if ($criterion[1] !== null) {
                    $predicates .= " :". str_ireplace('.', '', $criterion[0]);
                    $parameters[str_ireplace('.', '', $criterion[0])] = $criterion[1];
                }
                
                if ($key == 0) {
                    $queryBuilder = $queryBuilder->where($predicates);
                }elseif ($key > 0 && count($criterion) < 4) {
                    $queryBuilder = $queryBuilder->andWhere($predicates);
                }else {
                    $queryBuilder = $queryBuilder->orWhere($predicates);
                }
            }
        }
        
        if (count($parameters) > 0) {
            $queryBuilder = $queryBuilder->setParameters($parameters);
        }
        
        if (count($orderBy) > 0) {
            foreach ($orderBy as $sort => $order) {
                $queryBuilder = $queryBuilder->orderBy('o.'.$sort, $order);
            }
        }
        
        if (is_int($limit)) {
            $queryBuilder = $queryBuilder->setMaxResults($limit);
        }
        
        if (is_int($offset)) {
            $queryBuilder = $queryBuilder->setFirstResult($offset);
        }
        
        $query = $queryBuilder->getQuery();
        
        if ($useCache === true){
            $query->useResultCache(true)->useQueryCache(true);
        }
        
        return $query;
    }
}