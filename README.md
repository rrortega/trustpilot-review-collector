# trustpilot-reviews-collector
  
Retrive data from Trustpilot.com
TrustpilotReviewCollector is a PHP class designed to fetch and parse  reviews and business profile
from Trustpilot.com for a specified business unit ID. It supports pagination
and allows customization of the number of reviews to fetch, sorting, and ordering.
The class uses Symfony\DomCrawler and Symfony\HttpClient. 
## Installation
You can install TrustpilotReviewCollector library via Composer:
```yml
composer require rrortega/trustpilot-review-collector
```

## Usage

Here's an example of how to use TrustpilotReviewCollector:

```php
use RRO\Review\Collector\TrustpilotReviewCollector;

$businessUnitId="www.google.com";
$count=5;
$orderby = 'time';
$order = 'desc'
$trustpilot = new TrustpilotReviewCollector($businessUnitId,$count, $orderby , $order );
$reviews = $trustpilot->getReviews();

// The fetched reviews include details such as review ID, user, avatar, verified status, title, URL, content, rating, time,answer and answer_time.

foreach($reviews as $r){

     // echo  $r["id"];
     // echo  $r["user"];
     // echo  $r["avatar"];
     // echo  $r["verified"]; //true if user is verified in truspilot
     // echo  $r["iso"];//user country iso2
     // echo  $r["title"];
     // echo  $r["url"];
     // echo  $r["body"];
     // echo  $r["rating"]; //1 to 5 float value
     // echo  $r["time"]; //ex: 2023-03-20T13:30:34.000Z
}

// The profile include details such as business, category, website, logo, rating, qualification, and total_reviews.

$details= $trustpilot->getReviews();
 // echo  $details["business"];
 // echo  $details["category"];
 // echo  $details["website"];
 // echo  $details["logo"];
 // echo  $details["rating"];
 // echo  $details["qualification"];
 // echo  $details["total_reviews"];
```

## Testing

You can run the unit tests for TrustpilotReviewCollector Library using PHPUnit:

```cli
./vendor/bin/phpunit
```


## License

TrustpilotReviewCollector Library is licensed under the MIT license. See the LICENSE file for more information. 

 
------
NO SEAS MALITO Y REGÁLAME UN CAFECITO 
👉 [https://ko-fi.com/rolycoffe](https://ko-fi.com/rolycoffe)

