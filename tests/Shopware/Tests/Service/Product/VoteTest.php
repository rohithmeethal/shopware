<?php

namespace Shopware\Tests\Service\Product;

use Shopware\Bundle\StoreFrontBundle\Struct\Product\Vote;
use Shopware\Tests\Service\TestCase;

class VoteTest extends TestCase
{
    private function createVotes($articleId, $points = array())
    {
        $data = array(
            'id' => null,
            'articleID' => $articleId,
            'name' => 'Bert Bewerter',
            'headline' => 'Super Artikel',
            'comment' => 'Dieser Artikel zeichnet sich durch extreme Stabilität aus und fasst super viele Klamotten. Das Preisleistungsverhältnis ist exorbitant gut.',
            'points' => '5',
            'datum' => '2012-08-29 14:02:24',
            'active' => '1'
        );

        foreach ($points as $point) {
            $data['points'] = $point;

            Shopware()->Db()->insert('s_articles_vote', $data);
        }
    }

    public function testVoteList()
    {
        $number = 'testVoteList';
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);
        $product = $this->helper->createArticle($data);

        $points = array(1,2,2,3,3);
        $this->createVotes($product->getId(), $points);

        $listProduct = Shopware()->Container()->get('list_product_service_core')->get($number, $context);
        $votes = Shopware()->Container()->get('vote_service_core')->get($listProduct, $context);

        $this->assertCount(5, $votes);

        /**@var $vote Vote*/
        foreach ($votes as $vote) {
            $this->assertEquals('Bert Bewerter', $vote->getName());
        }
    }


    public function testVoteAverage()
    {
        $number = 'testVoteAverage';
        $context = $this->getContext();
        $data = $this->getProduct($number, $context);
        $product = $this->helper->createArticle($data);

        $points = array(1,2,2,3,3,3,3,3);
        $this->createVotes($product->getId(), $points);

        $listProduct = Shopware()->Container()->get('list_product_service_core')->get($number, $context);
        $voteAverage = Shopware()->Container()->get('vote_service_core')->getAverage($listProduct, $context);

        $this->assertEquals(5, $voteAverage->getAverage());

        foreach ($voteAverage->getPointCount() as $pointCount) {
            switch ($pointCount['points']) {
                case 1:
                    $this->assertEquals(1, $pointCount['total']);
                    break;
                case 2:
                    $this->assertEquals(2, $pointCount['total']);
                    break;
                case 3:
                    $this->assertEquals(5, $pointCount['total']);
                    break;
            }
        }
    }

}
