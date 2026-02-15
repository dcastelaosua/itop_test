<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* pages/backoffice/setuppage/layout.html.twig */
class __TwigTemplate_4bb542216838ae03a63e91cd21a2fd3b extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->blocks = [
            'iboPageBodyHtml' => [$this, 'block_iboPageBodyHtml'],
        ];
    }

    protected function doGetParent(array $context): bool|string|Template|TemplateWrapper
    {
        // line 3
        return "pages/backoffice/webpage/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        $this->parent = $this->loadTemplate("pages/backoffice/webpage/layout.html.twig", "pages/backoffice/setuppage/layout.html.twig", 3);
        yield from $this->parent->unwrap()->yield($context, array_merge($this->blocks, $blocks));
    }

    // line 5
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageBodyHtml(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 6
        yield "    <div id=\"ibo_setup_container\">
        ";
        // line 7
        yield $this->env->getFunction('render_block')->getCallable()(($context["oLayout"] ?? null), ["aPage" => ($context["aPage"] ?? null)]);
        yield "

        ";
        // line 10
        yield "        <div id=\"at_the_end\">";
        yield CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "sDeferredContent", [], "any", false, false, false, 10);
        yield "</div>
        <div style=\"display:none\" title=\"ex2\" id=\"ex2\">Please wait...</div>
        <div style=\"display:none\" title=\"dialog\" id=\"ModalDlg\"></div>
        <div style=\"display:none\" id=\"ajax_content\"></div>
    </div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/backoffice/setuppage/layout.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  66 => 10,  61 => 7,  58 => 6,  51 => 5,  40 => 3,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/backoffice/setuppage/layout.html.twig", "/var/www/html/itop/templates/pages/backoffice/setuppage/layout.html.twig");
    }
}
