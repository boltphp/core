<?php

class dom_documentTest extends Test {

    public function setUp() {
        $this->doc = new \bolt\dom\document();
    }

    public function testDefaultSettings() {
        $props = [
            'validateOnParse' => false,
            'preserveWhiteSpace' => false,
            'resolveExternals' => false,
            'substituteEntities' => false,
            'formatOutput' => false,
            'strictErrorChecking' => false,
        ];

        foreach ($props as $name => $value) {
            $this->eq($this->doc->$name, $value);
        }

    }

    public function testGetHtml() {
        $this->eq("<!DOCTYPE html>\n\n", $this->doc->html());
    }

    public function testSetHtml() {
        $html = '<div> hello world </div>';

        $this->eq($this->doc, $this->doc->html($html));
        $this->eq("<!DOCTYPE html>\n<html>$html</html>\n", $this->doc->html());

    }

    public function testSetHtmlWithDocType() {
        $html = '<!DOCTYPE html><html><body>poop</body></html>';
        $this->eq($this->doc, $this->doc->html($html));
        $this->eq("<!DOCTYPE html>\n<html><body>poop</body></html>\n", $this->doc->html());
    }

    public function testGuid() {
        $this->assertNotEquals($this->doc->refid, "");
    }

    public function testCleanDocument() {
        $this->doc->html("<html><body><div data-domref='9'>poop</div></body></html>");
        $this->eq("<!DOCTYPE html>\n<html><body><div>poop</div></body></html>\n", $this->doc->html());
    }

    public function testImportNode() {
        $node = new DOMElement('div', 'poop');
        $newNode = $this->doc->importNode($node);
        $this->eq($newNode->ownerDocument->refid, $this->doc->refid);
    }

    public function testImportElement() {
        $el = new bolt\dom\element('div', 'poop');
        $this->assertNotEquals($el->ownerDocument->refid, $this->doc->refid);
        $this->doc->import($el);
        $this->eq($el->ownerDocument->refid, $this->doc->refid);
    }

    public function testFindRef() {
        $el = new dom_documentTest_ref();
        $el->addClass('poop2');

        $this->doc->html("<div id='poop'></div>");

        $this->doc['#poop']->appendChild($el);

        $ref = $this->doc['.poop2']->first();

        $this->assertTrue(is_a($ref, 'dom_documentTest_ref'));

    }

}

class dom_documentTest_ref extends \bolt\dom\element {

}


