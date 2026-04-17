<?php

use App\Http\Controllers\ChatController;

it('preserves latex while converting markdown to html', function () {
    $controller = new class extends ChatController
    {
        public function render(string $content): string
        {
            return $this->renderAssistantHtml($content);
        }
    };

    $html = $controller->render(<<<'MARKDOWN'
### Electrochemistry

- Cell potential: $E^\circ_{\text{cell}} = 1.83\,\text{V}$

$$
\ce{Br2(aq) + Zn(s) -> Zn^2+(aq) + 2Br^-(aq)}
$$
MARKDOWN);

    expect($html)
        ->toContain('<h3>Electrochemistry</h3>')
        ->toContain('<li>Cell potential: $E^\circ_{\text{cell}} = 1.83\,\text{V}$</li>')
        ->toContain('$$'."\n".'\ce{Br2(aq) + Zn(s) -&gt; Zn^2+(aq) + 2Br^-(aq)}'."\n".'$$')
        ->not->toContain('<em>text{cell}</em>');
});
