<?php

namespace Hailkongsan\AntiBotLink;

use Illuminate\Support\Arr;
use Intervention\Image\ImageManagerStatic as Image;

class AntiBotLink
{
    /**
     * The Laravel session instace.
     *
     * @var \Hailkongsan\AntiBotLink\AntiBotLinkSessionManager
     */
    protected $session;

    /**
     * The Image instace.
     *
     * @var Intervention\Image\ImageManagerStatic
     */
    protected $image;

    /**
     * Font path.
     *
     * @var string
     */
    protected $font;

    /**
     * AntiBotLink data.
     *
     * @var array
     */
    protected $abl = [];

    public function __construct()
    {
        $this->session = app(AntiBotLinkSessionManager::class);
        $this->image = app(Image::class);

        $this->font = $this->getFontPath();
        $this->abl = $this->session->get('', []);
    }

    /**
     * Generate captcha.
     *
     * @param  bool $force
     * @return bool
     */
    public function generate($force = false)
    {
        if (! $force && ! $this->shouldGenerate()) {
            return true;
        }

        $words = $this->getWords();
        $this->abl['data'] = $this->generateRandomQuestionAndAnswer($words);

        $this->abl['links'] = $this->drawAnswer(Arr::pluck($this->abl['data'], 'answer'));

        $this->abl['question'] = $this->drawQuestion(implode(',', Arr::pluck($this->abl['data'], 'question')));

        $this->session->put([
            'data' => $this->abl['data'],
            'links' => $this->getLinks(),
            'question' => (string) $this->abl['question'],
            'solution' => trim(implode(' ', Arr::pluck($this->abl['data'], 'solution'))),
            'expire_at' => now()->addSeconds(config('antibotlink.expire', 120)),
        ]);

        return true;
    }

    protected function shouldGenerate()
    {
        return $this->isExpired()
            || ! $this->session->has()
            || ! $this->session->has('links')
            || ! $this->session->has('question')
            || ! $this->session->has('solution')
            || count($this->session->get('links')) != config('antibotlink.links');
    }

    public function verify($solution): bool
    {
        if ($this->isExpired() || empty($solution)) {
            return false;
        }

        if ($solution === $this->session->get('solution')) {
            $this->session->clear();

            return true;
        }

        return false;
    }

    protected function isExpired()
    {
        $time = $this->session->get('expire_at');

        if (! $time || now() > $time) {
            return true;
        }

        return false;
    }

    public function renderJS($form_selector, $name = 'antibotlink')
    {
        $js = '
		<script>
			(function ()
			{
				const click = '.config('antibotlink.links').';
				let resetBtn = document.getElementById("antibotlink_reset");
				let clicked = [];
			    let input = document.createElement("input");
			    input.setAttribute("name", "'.$name.'");
			    input.setAttribute("type", "hidden");
			    input.setAttribute("value", "");

				const hideResetBtn = () => {
					if (resetBtn.style.visibility === "visible") {
			    		resetBtn.style.visibility = "hidden";
			    	}
				};

				const showResetBtn = () => {
					if (resetBtn.style.visibility === "hidden") {
			    		resetBtn.style.visibility = "visible";
			    	}
				};

			    const handleClick = (e) => {
			    	const solution = e.target.dataset.ablSolution;
			    	if (clicked.length < click && !clicked.includes(solution)) {
			    		clicked.push(solution);
			    		e.target.style.visibility  = "hidden";
			    		console.log(clicked);
						
						showResetBtn();
			    	}
			    };

			    const handleSubmit = (e) => {
					e.preventDefault();
					console.log(input);
					input.setAttribute("value", clicked.join(" "));
					e.target.appendChild(input);
					e.target.submit();
			    };

			    

			    let form = document.querySelector("'.$form_selector.'");
			    form.addEventListener("submit", handleSubmit);

			    const links = document.querySelectorAll(".antibotlink");
			    links.forEach((link) => {
			        link.addEventListener("click", handleClick);
			    });
				
				const handleReset = (e) => {
					clicked = [];
					links.forEach((link) => {
						link.style.visibility = "visible";
					});
					hideResetBtn();
			    };

				resetBtn.addEventListener("click", handleReset);

			})();
		</script>
		';

        return $js;
    }

    /**
     * @param  [type] $offset [description]
     * @param  array  $class  [description]
     * @return [type]         [description]
     */
    public function renderLink($offset, $class = [])
    {
        $class[] = 'antibotlink';
        $solution = $this->abl['data'][$offset]['solution'];

        $link = $this->getLink($offset);

        return '<img class="'.implode(' ', $class).'" src="'.$link.'" data-abl-solution="'.$solution.'">';
    }

    /**
     * @param  [type] $question [description]
     * @return string
     */
    protected function drawQuestion($question)
    {
        $color = $this->generateColor();
        $width = $this->getWidth(strlen($question));
        $x = (int) $width / 2;
        $y = 13;

        $image = $this->image->canvas($width, $y * 2)
            ->text($question, $x, $y, function ($font) use ($color) {
                $font->file($this->font);
                $font->size(20);
                $font->color($color);
                $font->align('center');
                $font->valign('middle');
            });

        if (config('antibotlink.noise')) {
            for ($i = 0; $i < strlen($question) * 3; $i++) {
                $x = mt_rand(4, $width - 4);
                $y = mt_rand(3, 40 - 3);
                $image->line(
                        $x,
                        $y,
                        $x + mt_rand(-4, 4),
                        $y + mt_rand(-3, 3),
                        function ($image) use ($color) {
                            $image->color($color);
                        }
                    );
            }
        }
        $this->abl['data']['question_width'] = $width;

        return (string) $image->encode('data-url');
    }

    public function getSolution()
    {
        return $this->session->get('solution');
    }

    /**
     * [getLink description].
     * @param  [type] $offset [description]
     * @return [type]         [description]
     */
    public function getLink($offset)
    {
        if (Arr::has($this->abl, 'links.'.$offset)) {
            return (string) $this->abl['links'][$offset];
        }
    }

    /**
     * [getLinks description].
     * @return [type] [description]
     */
    public function getLinks()
    {
        if (Arr::has($this->abl, 'links')) {
            return $this->abl['links'];
        }

        return [];
    }

    public function getQuestion()
    {
        if (array_key_exists('question', $this->abl)) {
            return $this->abl['question'];
        }
    }

    /**
     * [getInstruction description].
     * @return [type] [description]
     */
    public function renderInstruction()
    {
        $img_url = $this->abl['question'];

        $width = $this->abl['data']['question_width'];

        $instruction = config('antibotlink.captcha_instruction');

        $instruction = str_replace(':instruction',
                trans('antibotlink::messages.instruction'), $instruction);

        $instruction = str_replace(':image',
                '<img src="'.$img_url.'" width="'.$width.'">', $instruction);

        return $instruction;
    }

    protected function generateRandomQuestionAndAnswer($words)
    {
        $result = [];

        foreach ($this->shuffleAssoc($words) as $question => $answer) {
            if (count($result) >= config('antibotlink.links')) {
                break;
            }

            $result[] = [
                'question' => $question,
                'answer' => $answer,
                'solution' => mt_rand(1000, 9999),
            ];
        }

        return $result;
    }

    /**
     * [getWords description].
     * @return [type] [description]
     */
    protected function getWords()
    {
        $words = config('antibotlink.words');

        return Arr::random($words);
    }

    /**
     * [drawImage description].
     * @return [type] [description]
     */
    protected function drawAnswer($answers)
    {
        $images = [];

        foreach ($answers as $answer) {
            $color = $this->generateColor();
            $width = $this->getWidth(strlen($answer));
            $x = (int) $width / 2;
            $y = 20;
            $image = $this->image->canvas($width, 40)
                ->text($answer, $x, $y, function ($font) use ($color) {
                    $font->file($this->font);
                    $font->size(mt_rand(20, 24));
                    $font->color($color);
                    $font->align('center');
                    $font->valign('middle');
                    $font->angle(mt_rand(-20, 20));
                });

            if (config('antibotlink.noise')) {
                for ($i = 0; $i < mt_rand(10, 25); $i++) {
                    $x = mt_rand(4, $width - 4);
                    $y = mt_rand(3, 40 - 3);
                    $image->line(
                        $x,
                        $y,
                        $x + mt_rand(-4, 4),
                        $y + mt_rand(-3, 3),
                        function ($image) use ($color) {
                            $image->color($color);
                        }
                    );
                }
            }

            $images[] = (string) $image->encode('data-url');
        }

        return $images;
    }

    protected function getFontPath()
    {
        $path = __DIR__.'/assets/fonts';
        $font = Arr::random(array_diff(scandir($path), ['..', '.']));

        return $path.DIRECTORY_SEPARATOR.pathinfo($font, PATHINFO_BASENAME);
    }

    protected function getWidth($length)
    {
        $length = $length == 1 ? $length + 1 : $length;

        return ($length + 2) * 11;
    }

    /**
     * Generate RGB color.
     *
     * @return array
     */
    protected function generateColor()
    {
        return [
            mt_rand(5, 50),
            mt_rand(5, 50),
            mt_rand(5, 50),
        ];
    }

    public function getSession()
    {
        return $this->session;
    }

    public function getImageInstance()
    {
        return $this->image;
    }

    private function shuffleAssoc(array $array)
    {
        $keys = array_keys($array);

        shuffle($keys);

        $random = [];

        foreach ($keys as $key) {
            $random[$key] = $array[$key];
        }

        return $random;
    }
}
