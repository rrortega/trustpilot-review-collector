<?php
use PHPUnit\Framework\TestCase;
class TrustpilotReviewsCollectorTest extends TestCase
{
    public function testGetData()
    {
        $id = "www.google.com";
        $count = 1;
        $collector = new TrustpilotReviewsCollector($id, $count);
        $list = $collector->getReviews();
        return $this->assert()
        // Add your assertions here to test the returned data
    }
}