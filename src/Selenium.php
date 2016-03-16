<?php

namespace SimpleHelpers;

class Selenium extends \PHPUnit_Extensions_Selenium2TestCase
{
    const BY_CSS_SELECTOR = 'byCssSelector';
    const BY_ID = 'byId';
    const BY_CLASS_NAME = 'byClassName';
    const BY_NAME = 'byName';
    const BY_TAG = 'byTag';
    const BY_LINK_TEXT = 'byLinkText';
    const BY_XPATH = 'byXPath';

    const TIMEOUT_COMMON = 25000;
    const TIMEOUT_ELEMENT = 15000;
    const TIMEOUT_ELEMENT_COUNT = 1000;

    /**
     * @param string $script
     */
    public function executeJavaScript($script)
    {
        $this->execute(['script' => $script, 'args' => []]);
    }

    protected function waitForUserInput()
    {
        echo 'Please press enter to continue...';

        if (trim(fgets(fopen('php://stdin', 'r'))) != chr(13)) {
            return;
        }
    }

    /**
     * @param string $by
     * @param string $value
     * @param boolean $displayed
     * @param integer $timeout
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function waitUntilStatus($by, $value, $displayed = true, $timeout = 0)
    {
        try {
            /** @var \PHPUnit_Extensions_Selenium2TestCase_Element $element */
            return $this->waitUntil(function() use ($by, $value, $displayed) {
                /** @var \PHPUnit_Extensions_Selenium2TestCase_Element $element */
                $element = $this->$by($value);

                if ($displayed) {
                    return $element->displayed() && $element->enabled() ? $element : null;
                }

                return !$element->displayed() ? $element : null;
            }, empty($timeout) ? self::TIMEOUT_ELEMENT : $timeout);
        } catch (\Exception $exception) {
            $this->fail(
                $exception->getMessage() . ' | ' .
                __FUNCTION__ . ' | ' . $by . ' | ' . $value . ' | ' . ($displayed ? 'displayed' : 'hidden')
            );

            return $this->$by($value);
        }
    }

    /**
     * @param string $by
     * @param string $value
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function waitUntilHide($by, $value)
    {
        return $this->waitUntilStatus($by, $value, false);
    }

    /**
     * @param string $by
     * @param string $value
     * @param integer $timeout
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function waitUntilShow($by, $value, $timeout = 0)
    {
        return $this->waitUntilStatus($by, $value, true, $timeout);
    }

    /**
     * @param string$value
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function getHiddenElementByCSS($value)
    {
        return $this->waitUntilHide(self::BY_CSS_SELECTOR, $value);
    }

    /**
     * @param string $value
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function getHiddenElementById($value)
    {
        return $this->waitUntilHide(self::BY_ID, $value);
    }

    /**
     * @param string $by
     * @param string $value
     * @param integer $timeout
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function clickDisplayedElement($by, $value, $timeout = 0)
    {
        $element = $this->waitUntilShow($by, $value, $timeout);

        if (null !== $element) {
            $this->moveto($element);
            $this->click(\PHPUnit_Extensions_Selenium2TestCase_SessionCommand_Click::LEFT);
        }

        return $element;
    }

    /**
     * @param string $value
     * @param integer $timeout
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function clickDisplayedElementByCSS($value, $timeout = 0)
    {
        return $this->clickDisplayedElement(self::BY_CSS_SELECTOR, $value, $timeout);
    }

    /**
     * @param string $value
     * @param integer $timeout
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function clickDisplayedElementByClass($value, $timeout = 0)
    {
        return $this->clickDisplayedElement(self::BY_CLASS_NAME, $value, $timeout, $timeout);
    }

    /**
     * @param string $value
     * @param integer $timeout
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function clickDisplayedElementByID($value, $timeout = 0)
    {
        return $this->clickDisplayedElement(self::BY_ID, $value, $timeout, $timeout);
    }

    /**
     * @param string $value
     * @param integer $timeout
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function clickDisplayedElementByName($value, $timeout = 0)
    {
        return $this->clickDisplayedElement(self::BY_NAME, $value, $timeout);
    }

    /**
     * @param string $value
     * @param integer $timeout
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function clickDisplayedElementByTag($value, $timeout = 0)
    {
        return $this->clickDisplayedElement(self::BY_TAG, $value, $timeout);
    }

    /**
     * @param string $value
     * @param integer $timeout
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function clickDisplayedElementByLinkText($value, $timeout = 0)
    {
        return $this->clickDisplayedElement(self::BY_LINK_TEXT, $value, $timeout);
    }

    /**
     * @param string $value
     * @param integer $timeout
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function clickDisplayedElementByXPath($value, $timeout = 0)
    {
        return $this->clickDisplayedElement(self::BY_XPATH, $value, $timeout);
    }

    /**
     * @param string $value
     * @param string $elementId
     * @param integer $timeout
     *
     * @return \PHPUnit_Extensions_Selenium2TestCase_Element
     */
    public function selectOptionByValueOfElementById($value, $elementId, $timeout = 0)
    {
        $element = $this->clickDisplayedElementByID($elementId, $timeout);

        \PHPUnit_Extensions_Selenium2TestCase_Element_Select::fromElement($element)->selectOptionByValue($value);

        return $element;
    }

    /**
     * @param string $by
     * @param string $value
     *
     * @return integer
     */
    public function countElementsBy($by, $value)
    {
        try {
            return count(
                $this->elements(
                    $this->using($by . ' selector')->value($value)
                )
            );
        } catch(\Exception $exception) {
            return 0;
        }
    }

    /**
     * @param string $by
     * @param string $value
     * @param integer $timeout
     *
     * @return boolean
     */
    public function hasElementBy($by, $value, $timeout = self::TIMEOUT_ELEMENT_COUNT)
    {
        $lastTimeout = $this->timeouts()->getLastImplicitWaitValue();

        $this->timeouts()->implicitWait($timeout);

        $has = $this->countElementsBy($by, $value) > 0;

        $this->timeouts()->implicitWait($lastTimeout);

        return $has;
    }
}
