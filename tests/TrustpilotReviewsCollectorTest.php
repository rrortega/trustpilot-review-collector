<?php  
 
namespace Tests; 
use PHPUnit\Framework\TestCase;
use RRO\Review\Collector\TrustpilotReviewCollector;

class TrustpilotReviewCollectorTest extends TestCase
{
    public function testAll()
    {
        $businessUnitId = "www.lovecuba.com";
        $count = 1;
        $collector = new TrustpilotReviewCollector($businessUnitId, $count);
        $list = $collector->getReviews();

        $this->assertNotEmpty($list); 
    }


}