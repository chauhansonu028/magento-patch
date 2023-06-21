<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Model;

use GraphQL\InlineFragment;
use GraphQL\QueryBuilder\QueryBuilder as GraphQLQueryBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\LiveSearchAdapter\Model\QueryArgumentProcessor\QueryArgumentProcessorResolver;

/**
 * Class QueryBuilder
 *
 * Build GraphQL query
 */
class QueryBuilder
{
    /**
     * @var array
     */
    private const QUERY_ARGUMENTS = [
        'phrase',
        'filter',
        'sort',
        'current_page',
        'page_size',
        'context',
    ];

    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * @var QueryArgumentProcessorResolver
     */
    private QueryArgumentProcessorResolver $queryArgumentProcessorResolver;

    /**
     * @param Json $serializer
     * @param QueryArgumentProcessorResolver $queryArgumentProcessorResolver
     */
    public function __construct(
        Json $serializer,
        QueryArgumentProcessorResolver $queryArgumentProcessorResolver
    ) {
        $this->serializer = $serializer;
        $this->queryArgumentProcessorResolver = $queryArgumentProcessorResolver;
    }

    /**
     * Build GraphQL query from Search Criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return string
     */
    public function build(SearchCriteriaInterface $searchCriteria): string
    {
        $builder = new GraphQLQueryBuilder('productSearch');

        $this->setQuerySelectFields($builder);
        $variables = [];

        foreach (self::QUERY_ARGUMENTS as $argumentName) {
            $queryArgumentProcessor = $this->queryArgumentProcessorResolver->resolve($argumentName);
            $argumentValue = $queryArgumentProcessor->getQueryArgumentValue($searchCriteria);

            if (!empty($argumentValue) || $queryArgumentProcessor->isRequired()) {
                $queryArgumentProcessor->setQueryArguments($builder);
                $variables[$argumentName] = $argumentValue;
            }
        }

        $data = ['query' => (string)$builder->getQuery(), 'variables' => $variables];

        return $this->serializer->serialize($data);
    }

    /**
     * Set fields for select query
     *
     * @param GraphQLQueryBuilder $builder
     * @return void
     */
    private function setQuerySelectFields(GraphQLQueryBuilder $builder): void
    {
        $builder->selectField('total_count');
        $builder->selectField(
            (new GraphQLQueryBuilder('items'))
                ->selectField(
                    (new GraphQLQueryBuilder('product'))
                        ->selectField('uid')
                        ->selectField('sku')
                        ->selectField('name')
                        ->selectField('canonical_url')
                        ->selectField(
                            (new GraphQLQueryBuilder('image'))
                                ->selectField('url')
                        )
                        ->selectField(
                            (new GraphQLQueryBuilder('price_range'))
                                ->selectField(
                                    (new GraphQLQueryBuilder('minimum_price'))
                                        ->selectField(
                                            (new GraphQLQueryBuilder('regular_price'))
                                                ->selectField('value')
                                        )
                                )
                        )
                )
        );

        $builder->selectField(
            (new GraphQLQueryBuilder('facets'))
                ->selectField('title')
                ->selectField('attribute')
                ->selectField(
                    (new GraphQLQueryBuilder('buckets'))
                        ->selectField('__typename')
                        ->selectField(
                            (new InlineFragment('ScalarBucket'))
                                ->setSelectionSet(
                                    [
                                        'id',
                                        'count',
                                        'title'
                                    ]
                                )
                        )
                        ->selectField(
                            (new InlineFragment('StatsBucket'))
                                ->setSelectionSet(
                                    [
                                        'min',
                                        'max',
                                        'title'
                                    ]
                                )
                        )
                        ->selectField(
                            (new InlineFragment('RangeBucket'))
                                ->setSelectionSet(
                                    [
                                        'from',
                                        'to',
                                        'count',
                                        'title'
                                    ]
                                )
                        )
                )
        );
    }
}
