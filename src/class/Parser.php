<?php

namespace Com\PaulDevelop\Library\Xml;

use Com\PaulDevelop\Library\Common\EventHandler;
use Com\PaulDevelop\Library\Common\EventHandlerCollection;
use Com\PaulDevelop\Library\Modeling\Xml\Attribute;
use Com\PaulDevelop\Library\Modeling\Xml\AttributeCollection;
use Com\PaulDevelop\Library\Modeling\Xml\CData;
use Com\PaulDevelop\Library\Modeling\Xml\Comment;
use Com\PaulDevelop\Library\Modeling\Xml\Tag;
use Com\PaulDevelop\Library\Modeling\Xml\Text;

class Parser
{
    #region events
    /**
     * TagParsedHandler
     *
     * @var EventHandlerCollection
     */
    private $tagParsedHandler;
    #endregion

    #region member
    private $content;
    #endregion

    #region constructor
    public function __construct()
    {
        $this->content = "";
        //this.OnTagParsed += new TagParsedHandler(Parser_OnTagParsed);

        $this ->tagParsedHandler = new EventHandlerCollection();
        $this->registerTagParsedEvent($this, 'Parser_OnTagParsed');
        //$this->RegisterEvent(null, 'Parser_OnTagParsed');
    }
    #endregion

    #region methods
    public function registerTagParsedEvent($object = null, $method = '')
    {
        $this->tagParsedHandler->add(new EventHandler($object, $method));
    }

    /**
     * throwTagParsedEvent.
     *
     * @param TagParsedEventArgs $e
     */
    protected function throwTagParsedEvent($e)
    {
        foreach ($this->tagParsedHandler as $tagParsedHandler) {
            if ($tagParsedHandler->Object != null) {
                call_user_func(array($tagParsedHandler->Object, $tagParsedHandler->Method), $this, $e);
            } else {
                call_user_func($tagParsedHandler->Method, $this, $e);
            }
        }
    }

    /**
     * Load from file.
     *
     * @param string $filename
     *
     * @return void
     */
    public function loadFromFile($filename = '')
    {
        // --- action ---
        $this->content = file_get_contents($filename);
    }

    public function loadFromXml($content = '')
    {
        // --- action ---
        $this->content = $content;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function parse()
    {
        // init
        $result = array();

        // action
        if ($this->content == '') {
            throw new \Exception('Empty xml document.');
        }

        // load xml document
        $xmlDoc = simplexml_load_string($this->content);
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML($this->content);

        // parse child nodes
        foreach ($xmlDoc->childNodes as $node) { // documentElement->
            /*
            echo "nodeName: ".$node->nodeName."<br />\n";
            echo "nodeValue: ".$node->nodeValue."<br />\n";
            echo "nodeType: ".$node->nodeType."<br />\n";
            echo "namespaceURI: ".$node->namespaceURI."<br />\n";
            echo "prefix: ".$node->prefix."<br />\n";
            echo "localName: ".$node->localName."<br />\n";
            echo "baseURI: ".$node->baseURI."<br />\n";
            echo "textContent: ".$node->textContent."<br />\n";
            echo "xmlContent: ". $node->ownerDocument->SaveXML($node)."<br />\n";
            echo "<hr />\n";
            */
            array_push($result, $this->parseNode($node));
        }

        // return
        return $result[0];
    }

    /**
     * Parse node.
     *
     * @param XmlNode $node
     *
     * @return INode
     */
    private function parseNode($node = null)
    {
        // init
        $result = null;

        // action
        // parse node
        $_namespace = $node->prefix;
        $_name = $node->nodeName;
        $_attributes = new AttributeCollection();
        $_content = $node->ownerDocument->SaveXML($node); //node.InnerXml;//InnerText;
        if ($node->attributes != null) {
            foreach ($node->attributes as $attribute) {
                $name = $attribute->nodeName;
                $value = $attribute->nodeValue;
                $_attributes->add(new Attribute($name, $value), $name);
            }
        }

        // check node type
        if ($_name == '#comment') {
            $result = new Comment($node->nodeValue);
        } elseif ($_name == '#text') {
            $result = new Text($node->nodeValue);
        } elseif ($_name == '#cdata-section') {
            $result = new CData($node->nodeValue);
            //this.throwCDataParsedEvent(new CDataParsedEventArgs(new CData(_content)));
        } else {
            $result = new Tag($_namespace, $_name, $_attributes); //, $_content);
            $this->throwTagParsedEvent(
                new TagParsedEventArgs(
                    $result //new Tag($_namespace, $_name, $_attributes, $_content)
                )
            );
        }

        // check children nodes
        if ($result != null) {
            if (gettype($result) == 'object' && get_class($result) == 'Com\PaulDevelop\Library\Modeling\Xml\Tag') {
                // parse child nodes
                foreach ($node->childNodes as $childNode) {
                    $tmp = $this->parseNode($childNode);
                    $result->Nodes->add($tmp);
                }
            }
        }

        // return
        return $result;
    }
    #endregion

    #region event handler
    /**
     * Parser_OnTagParsed.
     *
     * @param object    $sender
     * @param TagParsedEventArgs $e
     */
    protected function Parser_OnTagParsed($sender, $e)
    {
    }
    #endregion
}
