<?php

namespace Hailkongsan\AntiBotLink;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Arr;

class AntiBotLink
{
	/**
	 * The Laravel session instace
	 * 
	 * @var Illuminate\Support\Facades\Session
	 */
	public $session;

	/**
	 * The Image instace
	 * 
	 * @var Intervention\Image\ImageManagerStatic
	 */
	protected $image;

	/**
	 * Font path
	 * 
	 * @var string
	 */
	protected $font;

	/**
	 * AntiBotLink data
	 * 
	 * @var array
	 */
	protected $abl = [];

	public function __construct()
	{
		$this->session = session();
		$this->image = app(Image::class);

		$this->font = $this->getFont();
		$this->abl = $this->session->get(config('antibotlink.session_key'), []);
	}

	/**
	 * Generate captcha
	 * 
	 * @param  boolean $force 
	 * @return boolean         
	 */
	public function generate($force = false)
	{
		if (!$force && !$this->shouldGenerate()) {
			return true;
		}

		$words = $this->getWords();
		$this->abl['data'] = $this->generateRandomQuestionAndAnswer($words);

		$this->abl['links'] = $this->drawAnswer(Arr::pluck($this->abl['data'], 'answer'));

		$this->abl['question'] = $this->drawQuestion(implode(',', Arr::pluck($this->abl['data'], 'question')));
		
		$this->storeToSession([
			'data' => $this->abl['data'],
			'links' => $this->getLinks(),
			'question' => (string) $this->abl['question'],
			'solution' => trim(implode(' ', Arr::pluck($this->abl['data'], 'solution'))),
			'expire_at' => now()->addSeconds(config('antibotlink.expire')),
		]);

		return true;
	}

	public function shouldGenerate()
	{
		return $this->isExpired() 
			|| !$this->session->has(config('antibotlink.session_key'))
			|| !$this->session->has(config('antibotlink.session_key').'.links')
			|| !$this->session->has(config('antibotlink.session_key').'.question')
			|| !$this->session->has(config('antibotlink.session_key').'.solution');
	}

	public function verify($solution)
	{
		if ($this->isExpired() || empty($solution)) {
			return false;
		}

		if ($solution === $this->session->get(config('antibotlink.session_key').'.solution')) {
			$this->clearSession();
			return true;
		}

		return false;
	}

	public function clearSession()
	{
		$this->session->forget(config('antibotlink.session_key'));
	}

	public function isExpired()
	{
		$time = $this->session->get(config('antibotlink.session_key').'.expire_at');

		if (!$time || now() > $time) {
			return true;
		}

		return false;
	}

	public function storeToSession($data)
	{
		$this->session->put(config('antibotlink.session_key'), $data);
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
	 * 
	 * 
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
	 *
	 * 
	 * @param  [type] $question [description]
	 * @return [type]           [description]
	 */
	public function drawQuestion($question)
	{
		$color = $this->generateColor();
		$width = $this->getWidth(strlen($question));
		$x = (int) $width / 2;
		$y = 13;

		$image = $this->image->canvas($width, $y * 2)
			->text($question, $x, $y, function($font) use ($color) {
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
						function($image) use ($color) {
							$image->color($color);
						}
					);
				}	
			}
		$this->abl['data']['question_width'] = $width;

		return (string) $image->encode('data-url');
	}

	/**
	 * [getLink description]
	 * @param  [type] $offset [description]
	 * @return [type]         [description]
	 */
	public function getLink($offset)
	{
		if (Arr::has($this->abl, 'links.' . $offset)) {
			return (string) $this->abl['links'][$offset];
		}

		return null;
	}
	/**
	 * [getLinks description]
	 * @return [type] [description]
	 */
	public function getLinks()
	{
		if (Arr::has($this->abl, 'links')) {
			return $this->abl['links'];
		}

		return [];
	}

	/**
	 * [getInstruction description]
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

	/**
	 * 
	 */
	public function generateRandomQuestionAndAnswer($words)
	{
		$result = [];

		foreach (Arr::shuffleAssoc($words) as $question => $answer) {
			if (count($result) >= config('antibotlink.links')) {
				break;
			}

			$result[] = [
				'question' => $question,
				'answer' => $answer,
				'solution' => mt_rand(1000, 9999)
			];
		}

		return $result;
	}

	/**
	 * [getWords description]
	 * @return [type] [description]
	 */
	public function getWords()
	{
		$words = config('antibotlink.words');
		return Arr::random($words);
	}

	/**
	 * [drawImage description]
	 * @return [type] [description]
	 */
	public function drawAnswer($answers)
	{
		$images = [];

		foreach ($answers as $answer) {
			$color = $this->generateColor();
			$width = $this->getWidth(strlen($answer));
			$x = (int) $width / 2;
			$y = 20;
			$image = $this->image->canvas($width, 40)
				->text($answer, $x, $y, function($font) use ($color) {
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
						function($image) use ($color) {
							$image->color($color);
						}
					);
				}
			}

			$images[] = (string) $image->encode('data-url');
		}

		return $images;
	}

	public function getFont()
	{
		$path = config('antibotlink.assets.font_path');
		$font = Arr::random(array_diff(scandir($path), ['..', '.']));

		return $path . DIRECTORY_SEPARATOR . pathinfo($font, PATHINFO_BASENAME);
	}

	public function getWidth($length)
	{
		$length = $length == 1 ? $length + 1 : $length;

		return ($length + 2) * 11;
	}

	/**
	 * Generate RGB color
	 * @return array
	 */
	public function generateColor()
	{
		return [
			mt_rand(5, 50),
			mt_rand(5, 50),
			mt_rand(5, 50)
		];
	}
}