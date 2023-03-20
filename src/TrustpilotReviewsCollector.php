<?php 

class TrustpilotReviewsCollector
{

    private $id;

    private $count;
 
    private $orderby;

    private $order;

    /**
     * Constructor.
     * @param string $id ID of Trustpilot account.
     * @param int $count defines the number of reviews to return. '-1' returns all reviews. Default: '-1'
     * @param string $order_by defines by which parameter to sort reviews. Default 'time' Accepts: 'time' or 'rating'
     * @param string $order Designates ascending or descending order of reviews. Default 'desc'. Accepts 'asc', 'desc'.
     */
    function __construct($id, $count = '-1', $orderby = 'time', $order = 'desc')
    {
        $this->id = $id;
        $this->count = $count;
        $this->orderby = $orderby;
        $this->order = $order;
    }

    /**
     * Retrieve the html content of the page.
     *
     * @param int    $page   Page number 
     */
    public function getData($page = 1)
    {

        $options = array(
            CURLOPT_CUSTOMREQUEST  => "GET",
            CURLOPT_POST           => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_MAXREDIRS      => 10,
        );

        $curl = curl_init("https://trustpilot.com/review/{$this->id}?languages=all" . (1 != $page ? "&page={$page}" : "") . "&sort=recency");
        curl_setopt_array($curl, $options);
        $data = curl_exec($curl);

        return $data;
    }

    /**
     * Check if reviews are paginated in multiple pages.
     *
     * @param DOMDocument $dom
     * @param string 
     */
    private function parse_pagination($dom)
    {
        $xpath = new DOMXpath($dom);

        $pagination = $xpath->query(".//*[contains(@name, 'pagination-button-')][not(contains(@name, 'pagination-button-next'))]");

        return $pagination->length ? $pagination->item($pagination->length - 1)->nodeValue : 1;
    }

    /**
     * Find and return the author of the review.
     *
     * @param DOMDocument $dom
     * @param DOMNode $context
     * @param string 
     */
    private function parse_consumer($dom, $context)
    {
        $xpath = new DOMXpath($dom);

        return $xpath->query(".//*[@data-consumer-name-typography]", $context)->item(0)->nodeValue ?: '';
    }
 /**
     * Return true if the author of the review is verified account in trusttpilot.com.
     *
     * @param DOMDocument $dom
     * @param DOMNode $context
     * @param string 
     */
    private function parse_user_verified($dom, $context):bool
    {
        $xpath = new DOMXpath($dom);
        return  null != $xpath->query(".//*[contains(@class, 'ic-verified-user-check')]", $context)->item(0) ;
    }
    /**
     * Find and return the author avatar of the review.
     *
     * @param DOMDocument $dom
     * @param DOMNode $context
     * @param string 
     */
    private function parse_avatar_url($dom, $context)
    {
        $xpath = new DOMXpath($dom);
        if (null != $xpath->query(".//*[@data-consumer-avatar-image]", $context)->item(0)) {
            $user_link = $xpath->query(".//*[@data-consumer-profile-link]", $context)->item(0);
            $user_link = !empty($user_link) ?  $user_link->getAttribute('href') ?? '' : '';
            return !empty($user_link)
                ? sprintf("https://user-images.trustpilot.com/%s/73x73.png", str_replace("/users/", "", $user_link))
                : '';
        }
        return '';
    }
    /**
     * Find and return the author of the review.
     *
     * @param DOMDocument $dom
     * @param DOMNode $context
     * @param string 
     */
    private function parse_iso($dom, $context)
    {
        //return $xpath->query(".//*[@data-consumer-name-typography]", $context)->item(0)->nodeValue ?: '';
        //a[name="consumer-profile"] div[data-consumer-country-typography="true"] span
    }

    /**
     * Find and return the title of the review.
     *
     * @param DOMDocument $dom
     * @param DOMNode $context
     * @param string 
     */
    private function parse_title($dom, $context)
    {
        $xpath = new DOMXpath($dom);

        return $xpath->query(".//*[@data-review-title-typography]", $context)->item(0)->nodeValue ?: '';
    }


    /**
     * Find and return review url.
     *
     * @param DOMDocument $dom
     * @param DOMNode $context
     * @param string 
     */
    private function parse_url($dom, $context)
    {
        $xpath = new DOMXpath($dom);

        return $xpath->query(".//*[@data-review-title-typography]", $context)->item(0)->getAttribute('href') ?: '';
    }

    /**
     * Find and return review content.
     *
     * @param DOMDocument $dom
     * @param DOMNode $context
     * @param string 
     */
    private function parse_content($dom, $context)
    {
        $xpath = new DOMXpath($dom);

        $content = $xpath->query(".//*[@data-service-review-text-typography]", $context);

        return $content->length ? $content->item(0)->nodeValue : '';
    }

    /**
     * Find and return the review rating.
     *
     * @param DOMDocument $dom
     * @param DOMNode $context
     * @param string 
     */
    private function parse_rating($dom, $context)
    {
        $xpath = new DOMXpath($dom);

        return $xpath->query(".//div[@data-service-review-rating]", $context)->item(0)->getAttribute('data-service-review-rating') ?: '';
    }

    /**
     * Find and return review date time.
     *
     * @param DOMDocument $dom
     * @param DOMNode $context
     * @param string 
     */
    private function parse_time($dom, $context)
    {
        $xpath = new DOMXpath($dom);

        return $xpath->query(".//*[@data-service-review-date-time-ago]", $context)->item(0)->getAttribute('datetime') ?: '';
    }

    /**
     * Find the data of the review
     *
     * @param DOMDocument $dom
     * @param DOMNode $context
     * @param string 
     */
    private function parse_data($dom, $context)
    {

        $parsed = array(
            'consumer' => $this->parse_consumer($dom, $context),
            'avatar' => $this->parse_avatar_url($dom, $context),
            'is_verified' => $this->parse_user_verified($dom, $context), 
            'title' => $this->parse_title($dom, $context),
            'url' => $this->parse_url($dom, $context),
            'content' => $this->parse_content($dom, $context),
            'rating' => $this->parse_rating($dom, $context),
            'time' => $this->parse_time($dom, $context),
        );

        return $parsed;
    }

    /**
     * Sort reviews by set parameter
     *
     * @param array $data Array of reviews
     */
    private function sort(&$data)
    {

        if ('time' === $this->orderby) {

            if ('desc' === $this->order) {
                return $data;
            };
            $sorted = usort($data, function ($a, $b) {
                return strtotime($a['time']) <=> strtotime($b['time']);
            });
        } else if ('rating' === $this->orderby) {
            $sorted = usort($data, function ($a, $b) {
                return 'asc' === $this->order ? ($a['rating'] <=> $b['rating']) : ($b['rating'] <=> $a['rating']);
            });
        };

        return $sorted;
    }

    /**
     * Collect all reviews from truspilot
      */
    public function getReviews()
    {
        $data = $this->getHtml();

        $dom = new DOMDocument('1.0');

        $dom->loadHTML($data, LIBXML_NOERROR);

        $parsed = [];

        $items = $dom->getElementsByTagName('article');

        $pagination = $this->parse_pagination($dom);

        foreach ($items as $item) {

            if (count($parsed) >= $this->count && $this->count != -1) :
                break;
            endif;

            $parsed[] = $this->parse_data($dom, $item);
        }

        if ($pagination > 1) {

            for ($page = 2; $page <= $pagination; $page++) {

                $data = $this->getHtml($page);

                $dom->loadHTML($data, LIBXML_NOERROR);

                foreach ($items as $item) {

                    if (count($parsed) >= $this->count && $this->count != -1) :
                        break;
                    endif;

                    $parsed[] = $this->parse_data($dom, $item);
                }
            }
        }

        $this->sort($parsed);

        return $parsed;
    }


   
 
}