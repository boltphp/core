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

    // public function testGetHtml() {    
    //     $this->eq("\n", $this->doc->html());
    // }

    // public function testSetHtml() {
    //     $html = '<div> hello world </div>';

    //     $this->eq($this->doc, $this->doc->html($html));
    //     $this->eq("<!DOCTYPE html>\n<html><body>$html</body></html>\n", $this->doc->html());

    // }

    // public function testSetHtmlWithDocType() {
    //     $html = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"><html><body>poop</body></html>';
    //     $this->eq($this->doc, $this->doc->html($html));
    //     $this->eq('<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">'."\n<html><body>poop</body></html>\n", $this->doc->html());
    // }

    // public function testGuid() {
    //     $this->assertNotEquals($this->doc->guid, "");
    // }

    public function testCleanDocument() {
        $this->doc->html("<div data-domref='9'>poop</div>");
        $this->eq("<!DOCTYPE html>\n<html><body><div>poop</div></body></html>\n", $this->doc->html());
    }

    // public function testImportNode() {
    //     $node = new DOMElement('div', 'poop');
    //     $newNode = $this->doc->importNode($node);
    //     $this->eq($newNode->ownerDocument->guid, $this->doc->guid);
    // }

    // public function testImportElement() {
    //     $el = new bolt\dom\element('div', 'poop');
    //     $this->assertNotEquals($el->ownerDocument->guid, $this->doc->guid);
    //     $this->doc->import($el);
    //     $this->eq($el->ownerDocument->guid, $this->doc->guid);
    // }

    // public function testFindRef() {
    //     $el = new dom_documentTest_ref();
    //     $el->addClass('poop2');

    //     $this->doc->html("<div id='poop'></div>");

    //     $this->doc['#poop']->appendChild($el);

    //     $ref = $this->doc['.poop2']->first();

    //     $this->assertTrue(is_a($ref, 'dom_documentTest_ref'));

    // }

}

class dom_documentTest_ref extends \bolt\dom\element {

}


