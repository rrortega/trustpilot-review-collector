<?php
/**
 * TrustpilotReviewCollector is a PHP class designed to fetch and parse reviews and business profile
 * from Trustpilot.com for a specified business unit ID. It supports pagination
 * and allows customization of the number of reviews to fetch, sorting, and ordering.
 * The class uses Symfony\DomCrawler and Symfony\HttpClient
 *
 * Example usage:
 * $trustpilot = new TrustpilotReviewCollector($businessUnitId, $count, $orderBy, $order);
 * $reviews = $trustpilot->getReviews();
 *
 * The fetched reviews include details such as review ID, user, avatar, verified status,iso,
 * title, URL, content, rating, time,answer and answer_time.
 *
 * $profile = $trustpilot->profileData();
 *
 * The profile include details such as business, category, website, logo, rating, qualification, and total_reviews.
 *
 * Public repository: https://github.com/rrortega/trustpilot-review-collector
 * Author: Rolando Rodriguez Ortega (rolymayo11@gmail.com)
 */

namespace RRO\Review\Collector;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;


class TrustpilotReviewCollector
{

    private $businessUnitId;

    private $count;

    private $orderBy;

    private $order;

    /**
     * Constructor.
     * @param string $businessUnitId ID of Trustpilot account.
     * @param int $count defines the number of reviews to return. '-1' returns all reviews. Default: '-1'
     * @param string $order_by defines by which parameter to sort reviews. Default 'time' Accepts: 'time' or 'rating'
     * @param string $order Designates ascending or descending order of reviews. Default 'desc'. Accepts 'asc', 'desc'.
     */
    function __construct($businessUnitId, $count = '-1', $orderBy = 'time', $order = 'desc')
    {
        $this->businessUnitId = $businessUnitId;
        $this->count = $count;
        $this->orderBy = $orderBy;
        $this->order = $order;
    }

    /**
     * Retrieve the html content of the page.
     *
     * @param int $page Page number
     */
    /**
     * Retrieve the html content of the page.
     *
     * @param int $page Page number
     */
    public function getPageHtml($page = 1)
    {
        $c = HttpClient::create(['timeout' => 120, "verify_peer" => false, "verify_host" => false]);
        $response = $c->request('GET', "https://trustpilot.com/review/{$this->businessUnitId}?languages=all" . (1 != $page ? "&page={$page}" : "") . "&sort=recency", [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
            ],
            "max_redirects" => 10
        ]);

        return $response->getContent(false);

    }

    /**
     * Retrieves and returns profile data of a business from Trustpilot using its businessUnitId.
     * @return array
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function profileData(): array
    {
        $c = HttpClient::create(['timeout' => 120, "verify_peer" => false, "verify_host" => false]);
        $response = $c->request('GET', "https://trustpilot.com/review/{$this->businessUnitId}", [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
            ],
            "max_redirects" => 10
        ]);
        $html = $response->getContent(false);
        $crawler = new Crawler($html);
        $overview = $crawler->filterXPath('.//*[@data-overview-section]')->first();

        $logo = $this->extractAttributeValue($overview, ".//a[@data-business-unit-header-profile-image-link]/picture/img", 'src');
        $rating = $this->extractText($overview, ".//p[@data-rating-typography]");
        $business = $this->extractText($overview, ".//*[@id='business-unit-title']/h1/span");
        $compound = $this->extractText($overview, ".//*[@id='business-unit-title']/h1/following-sibling::span[1]/span");
        $category = $this->extractText($overview, ".//*[@id='business-unit-title']/h1/following-sibling::span[1]/following-sibling::p[1]/a");
        $website = $this->extractAttributeValue($overview, ".//*[@data-business-unit-header-profile-link]/div/a", "href");

        if (!empty($website)) {
            $website = explode("?", $website);
            $website = $website[0];
        }

        return [
            "business" => $business,
            "category" => $category,
            "website" => $website,
            "logo" => $logo,
            "rating" => $rating,
            "qualification" => strtoupper(trim(preg_replace("/[^a-z]+/i", "", $compound))),
            "total_reviews" => preg_replace("/[^0-9]+/", "", $compound)
        ];
    }


    /**
     * Collect all reviews from truspilot
     */
    public function getReviews(): array
    {
        $html = $this->getPageHtml();
        $crawler = new Crawler($html);
        $parsed = [];
        $items = $crawler->filterXPath(".//article");
        $pagination = $this->parsePagination($crawler);
        $items->each(function (Crawler $item) use (&$parsed) {
            if (count($parsed) < $this->count || $this->count == -1) {
                $parsed[] = $this->parseReviewData($item);
            }

        });
        if ($pagination > 1 && (count($parsed) < $this->count || $this->count == -1)) {
            for ($page = 2; $page <= $pagination && (count($parsed) < $this->count || $this->count == -1); $page++) {
                $html = $this->getPageHtml($page);
                $crawler = new Crawler($html);
                $items = $crawler->filterXPath(".//article");
                $items->each(function (Crawler $item) use (&$parsed) {
                    if (count($parsed) < $this->count || $this->count == -1) {
                        $parsed[] = $this->parseReviewData($item);
                    }
                });
            }
        }
        $this->sort($parsed);
        return $parsed;
    }

    /**
     * Helper method to extract the text content of an element from a given XPath.
     *
     * @param Crawler $crawler The Crawler object to extract data from
     * @param string $xpath The XPath expression to find the element
     * @return string The extracted text content, or an empty string if the element is not found
     */
    private function extractText(Crawler $crawler, string $xpath): string
    {
        $node = $crawler->filterXPath($xpath);
        return $node->count() > 0 ? $node->text() : '';
    }

    /**
     * Helper method to extract the value of a specified attribute from the first element matching a given XPath.
     *
     * @param Crawler $crawler The Crawler object to extract data from
     * @param string $xpath The XPath expression to find the element
     * @param string $attribute The attribute to extract the value from
     * @return string The extracted attribute
     */
    private function extractAttributeValue(Crawler $crawler, string $xpath, string $attribute): string
    {
        $values = $crawler->filterXPath($xpath)->extract([$attribute]);
        return !empty($values) ? array_pop($values) : '';
    }

    /**
     * Check if reviews are paginated in multiple pages.
     *
     * @param Crawler $craw
     * @param string
     */
    private function parsePagination($craw)
    {
        $pagination = $this->extractText($craw, ".//*[contains(@name, 'pagination-button-')][not(contains(@name, 'pagination-button-next'))]");
        return !empty($pagination) ? $pagination : 1;
    }

    /**
     * Find and return the author of the review.
     * @param Crawler $item
     * @return string
     */
    private function getUserFullName(Crawler $item)
    {
        return $this->extractText($item, ".//*[@data-consumer-name-typography]");
    }

    /**
     * Return true if the author of the review is verified account in trusttpilot.com.
     * @param Crawler $item
     * @return bool
     */
    private function isUserVerified(Crawler $item): bool
    {
        return $item->filterXPath(".//*[contains(@class, 'ic-verified-user-check')]")->count() > 0;
    }

    /**
     *  Find and return the author avatar of the review.
     * @param Crawler $item
     * @return string
     */
    private function getAvatar(Crawler $item)
    {
        if ($item->filterXPath(".//*[@data-consumer-avatar-image]")->count()) {
            $user_link = $this->extractAttributeValue($item, ".//*[@data-consumer-profile-link]", 'href');
            return !empty($user_link)
                ? sprintf("https://user-images.trustpilot.com/%s/73x73.png", str_replace("/users/", "", $user_link))
                : '';
        }
        return '';
    }

    /**
     * Find and return the author country ISO
     * @param Crawler $item
     * @return string
     */
    private function getCountryIso(Crawler $item)
    {
        return $this->extractText($item, ".//*[@data-consumer-country-typography]/span");

    }

    /**
     * Find and return the title of the review.
     * @param Crawler $item
     * @return string
     */
    private function getTitle(Crawler $item)
    {
        return $this->extractText($item, ".//*[@data-review-title-typography]");
    }


    /**
     * Find and return review url.
     * @param Crawler $item
     * @return string
     */
    private function getUrl(Crawler $item)
    {
        $href = $this->extractAttributeValue($item, ".//*[@data-review-title-typography]", 'href');
        return "https://trustpilot.com$href";
    }

    /**
     * Find and return review id.
     * @param Crawler $item
     * @return array|string|string[]
     */
    private function getId(Crawler $item): string
    {
        $href = $this->extractAttributeValue($item, ".//*[@data-review-title-typography]", 'href');
        return str_replace("/reviews/", "", $href);
    }

    /**
     * Find and return review content.
     * @param Crawler $item
     * @return string
     */
    private function getBody(Crawler $item): string
    {
        return $this->extractText($item, ".//*[@data-service-review-text-typography]");
    }

    /**
     * Find and return review answer messaje.
     * @param Crawler $item
     * @return string
     */
    private function getAnswer(Crawler $item): string
    {
        return $this->extractText($item, ".//*[@data-service-review-business-reply-text-typography]");
    }

    /**
     * Find and return review answer time.
     * @param Crawler $item
     * @return string
     */
    private function getAnswerTime(Crawler $item): string
    {
        return $this->extractAttributeValue($item, ".//*[@data-service-review-business-reply-date-time-ago]", 'datetime');
    }

    /**
     * Find and return the review rating.
     * @param Crawler $item
     * @return mixed|string
     */
    private function getRating(Crawler $item): string
    {
        $rating = $this->extractAttributeValue($item, ".//div[@data-service-review-rating]", 'data-service-review-rating');
        return !empty($rating) ? $rating : '0';
    }

    /**
     * Find and return review date time.
     * @param Crawler $item
     * @return mixed|string
     */
    private function getTime(Crawler $item): string
    {
        return $this->extractAttributeValue($item, ".//*[@data-service-review-date-time-ago]", 'datetime');
    }

    /**
     * Find the data of the review
     * @param Crawler $item
     * @return array
     */
    private function parseReviewData(Crawler $item): array
    {
        $parsed = array(
            'id' => $this->getId($item),
            'user' => $this->getUserFullName($item),
            "iso" => $this->getCountryIso($item),
            'avatar' => $this->getAvatar($item),
            'verified' => $this->isUserVerified($item),
            'title' => $this->getTitle($item),
            'url' => $this->getUrl($item),
            'body' => $this->getBody($item),
            'rating' => $this->getRating($item),
            'time' => $this->getTime($item),
            'answer' => $this->getAnswer($item),
            'answer_time' => $this->getAnswerTime($item),
        );

        return $parsed;
    }

    /**
     * Sort reviews by set parameter
     *
     * @param array $data Array of reviews
     */
    private function sort(&$data): array
    {

        if ('time' === $this->orderBy) {
            if ('desc' === $this->order) {
                return $data;
            };
            $sorted = usort($data, function ($a, $b) {
                return strtotime($a['time']) <=> strtotime($b['time']);
            });
        } else if ('rating' === $this->orderBy) {
            $sorted = usort($data, function ($a, $b) {
                return 'asc' === $this->order ? ($a['rating'] <=> $b['rating']) : ($b['rating'] <=> $a['rating']);
            });
        };

        return $sorted;
    }
}
