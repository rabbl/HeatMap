<?php

namespace HeatMap;

class RainbowVis
{

    private $gradients = null;

    /** @var float  */
    private $minValue = 0;

    /** @var float  */
    private $maxValue = 100;

    /** @var array */
    private $spectrum = ['ff0000', 'ffff00', '00ff00', '0000ff'];

    /**
     * RainbowVis constructor.
     * @param array $spectrum
     * @param float $minValue
     * @param float $maxValue
     */
    public function __construct(array $spectrum, float $minValue, float $maxValue)
    {
        if ($minValue === $maxValue){
            $spectrum = [$spectrum[0]];
        }

        $this->setSpectrumByArray($spectrum);
        $this->setNumberRange(floor($minValue), ceil($maxValue));
    }

    /**
     * @param array $spectrum
     * @return $this
     */
    private function setColors(array $spectrum){

        $this->gradients = [];

        if (count($spectrum) < 1) {
            throw new \InvalidArgumentException(sprintf('Spectrum must have at least one colour, %s given.', count($spectrum)));
        }

        if (count($spectrum) == 1) {
            $gradient = new ColorGradient();
            $gradient->setGradient($spectrum[0], $spectrum[0]);
            $gradient->setNumberRange(0, 1);
            $this->gradients[] = $gradient;
            $this->spectrum = $spectrum;
            return $this;
        }

        $increment = ($this->maxValue - $this->minValue)/(count($spectrum)-1);
        for ($i=0; $i<count($spectrum)-1; $i++){
            $gradient = new ColorGradient();
            $gradient->setGradient($spectrum[$i], $spectrum[$i + 1]);
            $gradient->setNumberRange($this->minValue + $increment * $i, $this->minValue + $increment * ($i + 1));
            $this->gradients[] = $gradient;
        }

        $this->spectrum = $spectrum;
        return $this;
    }

    /**
     * @return $this
     */
    public function setSpectrum(){
        $this->setColors(func_get_args());
        return $this;
    }

    /**
     * @return array
     */
    public function getSpectrum(){
        return $this->spectrum;
    }

    /**
     * @param array $spectrum
     * @return $this
     */
    public function setSpectrumByArray(array $spectrum) {
        $this->setColors($spectrum);
        return $this;
    }

    public function colorAt(float $value){

        if ($value < $this->minValue || count($this->gradients) === 1){
            /** @var ColorGradient $gradient */
            $gradient = $this->gradients[0];
            return $gradient->colorAt($value);
        }

        /** @var ColorGradient $gradient */
        foreach ($this->gradients as $gradient){
            if ($gradient->getMinValue() <= $value && $gradient->getMaxValue() >= $value){
                return $gradient->colorAt($value);
            }
        }

        $gradient = $this->gradients[count($this->gradients)-1];
        return $gradient->colorAt($value);
    }

    /**
     * @param float $minValue
     * @param float $maxValue
     * @return $this
     */
    public function setNumberRange(float $minValue, float $maxValue){
        if ($minValue > $maxValue){
            throw new \InvalidArgumentException(sprintf('MaxValue %s is not greater then MinValue %s', $minValue, $maxValue));
        }

        $this->minValue = $minValue;
        $this->maxValue = $maxValue;

        $this->setColors($this->spectrum);

        return $this;
    }
}
