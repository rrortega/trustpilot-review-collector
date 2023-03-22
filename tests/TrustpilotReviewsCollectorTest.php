<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use RRO\Review\Collector\TrustpilotReviewCollector;

class TrustpilotReviewCollectorTest extends TestCase
{
    public function testReviews()
    {
        $businessUnitId = "www.google.com";
        $count = 1;
        $trustpilot = new TrustpilotReviewCollector($businessUnitId, $count);
        $list = $trustpilot->getReviews();
        $this->assertNotEmpty($list, "Review list empty");
        $this->assertCount(1, $list, "Expected a single element in the list");
    }

    public function testProfile()
    {
        $businessUnitId = "www.google.com";
        $count = 1;
        $trustpilot = new TrustpilotReviewCollector($businessUnitId, $count);
        $profile = $trustpilot->profileData();
        $this->assertNotEmpty($profile, "Data expected in profile");
        $this->assertArrayHasKey("business", $profile, "The profile was expected to have the business key");
        $this->assertArrayHasKey("category", $profile, "The profile was expected to have the category key");
        $this->assertArrayHasKey("website", $profile, "The profile was expected to have the website key");
        $this->assertArrayHasKey("logo", $profile, "The profile was expected to have the logo key");
        $this->assertArrayHasKey("rating", $profile, "The profile was expected to have the rating key");
        $this->assertArrayHasKey("qualification", $profile, "The profile was expected to have the qualification key");
        $this->assertArrayHasKey("total_reviews", $profile, "The profile was expected to have the total_reviews key");

        $this->assertNotEmpty($profile["business"], "business data expected in profile");
        $this->assertNotEmpty($profile["category"], "category data expected in profile");
        $this->assertNotEmpty($profile["website"], "website data expected in profile");
        $this->assertNotEmpty($profile["logo"], "logo data expected in profile");
        $this->assertNotEmpty($profile["rating"], "rating data expected in profile");
        $this->assertNotEmpty($profile["qualification"], "qualification data expected in profile");
        $this->assertNotEmpty($profile["total_reviews"], "total_reviews data expected in profile");
    }
}
