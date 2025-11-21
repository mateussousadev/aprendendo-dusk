<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SiconfiTest extends DuskTestCase
{
    public function test_web_scrape_siconfi()
    {
        $this->browse(function (Browser $browser) {
            $browser
                ->visit('https://siconfi.tesouro.gov.br/siconfi/pages/public/sti/iframe_sti.jsf')
                ->waitFor('#iframeCAUC', 10)

                ->withinFrame('#iframeCAUC', function (Browser $iframe) use ($browser) {
                    // Passo 1: Selecionar "I - Ente da Federação"
                    $iframe->waitFor('#tabview-consultas-publicas\\:formExtratos\\:wizard-extrato', 10)
                        ->waitForText('I - Ente da Federação', 10)
                        ->clickAtXPath('//label[contains(text(), "I - Ente da Federação")]')
                        ->pause(1500);

                    $browser->press('Próximo')->pause(1500);

                    // Verificar tela correta
                    $iframe->waitFor('#tabview-consultas-publicas\\:formExtratos\\:entesFederados_input', 10)
                        ->screenshot('3-campo-cnpj-visivel')

                        // Preencher campo visível
                        ->type('#tabview-consultas-publicas\\:formExtratos\\:entesFederados_input', '06.554.430/0001-31')
                        ->pause(1500) // Aumentar tempo para autocomplete carregar

                        // Aguardar e clicar na sugestão
                        ->waitFor('.ui-autocomplete-panel', 5)
                        ->screenshot('4-autocomplete-aberto')
                        ->clickAtXPath('//li[contains(text(), "Parnaíba/PI")]')
                        ->pause(1000) // Aumentar tempo para processar

                        // MUDANÇA: Verificar o campo VISÍVEL ao invés do hidden
                        ->assertInputValue(
                            '#tabview-consultas-publicas\\:formExtratos\\:entesFederados_input',
                            '06.554.430/0001-31 - Parnaíba/PI'
                        )

                        ->screenshot('5-valor-confirmado')

                        // PASSO: Clicar no hCaptcha
                        ->waitForText('i am human', 10)
                        ->screenshot('6-antes-captcha')

                        // Clicar no checkbox do hCaptcha
                        ->click('div[role="checkbox"]')

                        // Aguardar o hCaptcha ser verificado (pode demorar alguns segundos)
                        ->pause(3000)

                        // Aguardar o checkbox mudar para checked
                        ->waitUntil('document.querySelector(\'div[role="checkbox"]\').getAttribute("aria-checked") === "true"', 15)

                        ->screenshot('7-captcha-verificado');
                });
        });
    }
}
