<?php

namespace Com\PaulDevelop\Library\Xml;

use Com\PaulDevelop\Library\Common\EventArgs;
use Com\PaulDevelop\Library\Modeling\Xml\Tag;

class TagParsedEventArgs extends EventArgs
{
    #region member
    /**
     * Tag
     *
     * @var Tag
     */
    private $tag;
    #endregion

    #region constructor
    public function __construct($tag = null)
    {
        $this->tag = $tag;
    }
    #endregion

    #region properties
    /**
     * Tag.
     *
     * @return Tag
     */
    public function getTag()
    {
        return $this->tag;
    }
    #endregion
}
