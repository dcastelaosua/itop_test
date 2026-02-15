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

/* pages/backoffice/webpage/layout.html.twig */
class __TwigTemplate_a8fe9a22df371036ac028c0b454057c9 extends Template
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

        $this->parent = false;

        $this->blocks = [
            'iboPageExtraMetas' => [$this, 'block_iboPageExtraMetas'],
            'iboPageCssFiles' => [$this, 'block_iboPageCssFiles'],
            'iboPageCssInline' => [$this, 'block_iboPageCssInline'],
            'iboPageJsInlineEarly' => [$this, 'block_iboPageJsInlineEarly'],
            'iboPageBodyHtml' => [$this, 'block_iboPageBodyHtml'],
            'iboDeferredBlocks' => [$this, 'block_iboDeferredBlocks'],
            'iboPageTemplates' => [$this, 'block_iboPageTemplates'],
            'iboPageJsFiles' => [$this, 'block_iboPageJsFiles'],
            'iboPageJsInlineScripts' => [$this, 'block_iboPageJsInlineScripts'],
            'iboPageJsInlineOnInit' => [$this, 'block_iboPageJsInlineOnInit'],
            'iboPageJsInlineOnDomReady' => [$this, 'block_iboPageJsInlineOnDomReady'],
            'iboPageJsInlineLive' => [$this, 'block_iboPageJsInlineLive'],
            'iboCapturedOutput' => [$this, 'block_iboCapturedOutput'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 3
        yield "<!DOCTYPE html>
<html lang=\"";
        // line 4
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aMetadata", [], "any", false, false, false, 4), "sLang", [], "any", false, false, false, 4), "html", null, true);
        yield "\">
<head>
    <meta charset=\"";
        // line 6
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aMetadata", [], "any", false, false, false, 6), "sCharset", [], "any", false, false, false, 6), "html", null, true);
        yield "\">
    ";
        // line 8
        yield "    ";
        yield from $this->unwrap()->yieldBlock('iboPageExtraMetas', $context, $blocks);
        // line 10
        yield "    ";
        if ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aMetadata", [], "any", false, true, false, 10), "sBaseUrl", [], "any", true, true, false, 10) || CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aMetadata", [], "any", false, true, false, 10), "sBaseTarget", [], "any", true, true, false, 10))) {
            // line 11
            yield "        <base ";
            if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aMetadata", [], "any", false, true, false, 11), "sBaseUrl", [], "any", true, true, false, 11)) {
                yield "href=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aMetadata", [], "any", false, false, false, 11), "sBaseUrl", [], "any", false, false, false, 11), "html", null, true);
                yield "\"";
            }
            yield " ";
            if (CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aMetadata", [], "any", false, true, false, 11), "sBaseTarget", [], "any", true, true, false, 11)) {
                yield "target=\"";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aMetadata", [], "any", false, false, false, 11), "sBaseTarget", [], "any", false, false, false, 11), "html", null, true);
                yield "\"";
            }
            yield ">
    ";
        }
        // line 13
        yield "    <title>";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "sTitle", [], "any", false, false, false, 13), "html", null, true);
        yield "</title>
    ";
        // line 14
        if (CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "sFaviconUrl", [], "any", true, true, false, 14)) {
            // line 15
            yield "        <link rel=\"shortcut icon\" href=\"";
            yield $this->env->getFilter('add_itop_version')->getCallable()(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "sFaviconUrl", [], "any", false, false, false, 15));
            yield "\">
    ";
        }
        // line 17
        yield "    <link rel=\"search\" type=\"application/opensearchdescription+xml\" title=\"iTop\" href=\"";
        yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "sAbsoluteUrlAppRoot", [], "any", false, false, false, 17), "html", null, true);
        yield "pages/opensearch.xml.php\">
    ";
        // line 19
        yield "    ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aPreloadedFonts", [], "any", false, false, false, 19));
        foreach ($context['_seq'] as $context["_key"] => $context["aPreloadedFont"]) {
            // line 20
            yield "        <link rel=\"preload\" href=\"";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v0 = $context["aPreloadedFont"]) && is_array($_v0) || $_v0 instanceof ArrayAccess ? ($_v0["font"] ?? null) : null), "html", null, true);
            yield "\" as=\"font\" type=\"font/";
            yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v1 = $context["aPreloadedFont"]) && is_array($_v1) || $_v1 instanceof ArrayAccess ? ($_v1["type"] ?? null) : null), "html", null, true);
            yield "\" crossorigin>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['aPreloadedFont'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 22
        yield "    ";
        // line 25
        yield "    ";
        yield from $this->unwrap()->yieldBlock('iboPageCssFiles', $context, $blocks);
        // line 33
        yield "
    ";
        // line 34
        yield from $this->unwrap()->yieldBlock('iboPageCssInline', $context, $blocks);
        // line 42
        yield "
    ";
        // line 43
        yield from $this->unwrap()->yieldBlock('iboPageJsInlineEarly', $context, $blocks);
        // line 51
        yield "</head>
<body data-gui-type=\"backoffice\">
";
        // line 53
        if (CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "isPrintable", [], "any", false, false, false, 53)) {
            yield "<div class=\"printable-content\" style=\"width: 27.7cm;\">";
        }
        // line 54
        yield "    ";
        yield from $this->unwrap()->yieldBlock('iboPageBodyHtml', $context, $blocks);
        // line 59
        if (CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "isPrintable", [], "any", false, false, false, 59)) {
            yield "</div>";
        }
        // line 60
        yield "
";
        // line 61
        yield from $this->unwrap()->yieldBlock('iboDeferredBlocks', $context, $blocks);
        // line 66
        yield "
";
        // line 67
        yield from $this->unwrap()->yieldBlock('iboPageTemplates', $context, $blocks);
        // line 69
        yield "
";
        // line 74
        yield "<script type=\"text/javascript\">
/**
 * @var {Map} aLoadedCssFilesRegister
 * Register of all CSS files loaded in this page.
 * A CSS file MUST NOT be loaded more than once as it could compromise rules overloads loaded after the first time.
 */
const aLoadedCssFilesRegister = new Map();
";
        // line 81
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aCssFiles", [], "any", false, false, false, 81));
        foreach ($context['_seq'] as $context["_key"] => $context["aCssFileData"]) {
            // line 82
            yield "    aLoadedCssFilesRegister.set(\"";
            yield (($_v2 = $context["aCssFileData"]) && is_array($_v2) || $_v2 instanceof ArrayAccess ? ($_v2["link"] ?? null) : null);
            yield "\", true);
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['aCssFileData'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 84
        yield "</script>

";
        // line 89
        yield "<script type=\"text/javascript\">
/**
 * @var {Map} aLoadedJsFilesRegister
 * Register of all JS files loaded in this page, including the Promise corresponding to the loading state of each file.
 * A JS file MUST NOT be loaded more than once as it could compromise settings / plugins loaded after the first time.
 */
const aLoadedJsFilesRegister = new Map();
/**
 * @var {Map} aLoadedJsFilesResolveCallbacks
 * Resolve callbacks of JS files registered in aLoadedJsFilesRegister. Used -mostly in AjaxPage- to know when a file is done loading so the depending snippets can run
 */
const aLoadedJsFilesResolveCallbacks = new Map();
";
        // line 101
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aJsFiles", [], "any", false, false, false, 101));
        foreach ($context['_seq'] as $context["_key"] => $context["sJsFile"]) {
            // line 102
            yield "    aLoadedJsFilesRegister.set(\"";
            yield $context["sJsFile"];
            yield "\", new Promise(function(resolve) {
        aLoadedJsFilesResolveCallbacks.set(\"";
            // line 103
            yield $context["sJsFile"];
            yield "\", resolve);
        // Resolve promise right away as these files are loaded immediately before any inline JS is executed
        aLoadedJsFilesResolveCallbacks.get(\"";
            // line 105
            yield $context["sJsFile"];
            yield "\")();
    }));
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['sJsFile'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 108
        yield "</script>

";
        // line 110
        yield from $this->unwrap()->yieldBlock('iboPageJsFiles', $context, $blocks);
        // line 115
        yield "
";
        // line 116
        yield from $this->unwrap()->yieldBlock('iboPageJsInlineScripts', $context, $blocks);
        // line 146
        yield "
";
        // line 147
        yield from $this->unwrap()->yieldBlock('iboCapturedOutput', $context, $blocks);
        // line 150
        yield "
</body>
</html>";
        yield from [];
    }

    // line 8
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageExtraMetas(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 9
        yield "    ";
        yield from [];
    }

    // line 25
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageCssFiles(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 26
        yield "\t    ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aCssFiles", [], "any", false, false, false, 26));
        foreach ($context['_seq'] as $context["_key"] => $context["aCssFileData"]) {
            // line 27
            yield "            ";
            // line 28
            yield "\t\t    ";
            if (((($_v3 = $context["aCssFileData"]) && is_array($_v3) || $_v3 instanceof ArrayAccess ? ($_v3["condition"] ?? null) : null) != "")) {
                yield "<!--[if ";
                yield $this->env->getRuntime('Twig\Runtime\EscaperRuntime')->escape((($_v4 = $context["aCssFileData"]) && is_array($_v4) || $_v4 instanceof ArrayAccess ? ($_v4["condition"] ?? null) : null), "html", null, true);
                yield "]>";
            }
            // line 29
            yield "\t\t\t    <link type=\"text/css\" href=\"";
            yield $this->env->getFilter('add_itop_version')->getCallable()((($_v5 = $context["aCssFileData"]) && is_array($_v5) || $_v5 instanceof ArrayAccess ? ($_v5["link"] ?? null) : null));
            yield "\" rel=\"stylesheet\">
\t\t\t";
            // line 30
            if (((($_v6 = $context["aCssFileData"]) && is_array($_v6) || $_v6 instanceof ArrayAccess ? ($_v6["condition"] ?? null) : null) != "")) {
                yield "<![endif]-->";
            }
            // line 31
            yield "\t    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['aCssFileData'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 32
        yield "    ";
        yield from [];
    }

    // line 34
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageCssInline(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 35
        yield "        ";
        // line 36
        yield "        ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aCssInline", [], "any", false, false, false, 36));
        foreach ($context['_seq'] as $context["_key"] => $context["sCssInline"]) {
            // line 37
            yield "            <style>
                ";
            // line 38
            yield $context["sCssInline"];
            yield "
            </style>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['sCssInline'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 41
        yield "    ";
        yield from [];
    }

    // line 43
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageJsInlineEarly(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 44
        yield "        ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aJsInlineEarly", [], "any", false, false, false, 44));
        foreach ($context['_seq'] as $context["_key"] => $context["sJsInline"]) {
            // line 45
            yield "            ";
            // line 46
            yield "            <script type=\"text/javascript\">
            ";
            // line 47
            yield $context["sJsInline"];
            yield "
            </script>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['sJsInline'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 50
        yield "    ";
        yield from [];
    }

    // line 54
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageBodyHtml(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 55
        yield "        <div id=\"ibo-page-container\">
            ";
        // line 56
        yield $this->env->getFunction('render_block')->getCallable()(($context["oLayout"] ?? null), ["aPage" => ($context["aPage"] ?? null)]);
        yield "
        </div>
    ";
        yield from [];
    }

    // line 61
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboDeferredBlocks(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 62
        yield "    ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["aDeferredBlocks"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["oBlock"]) {
            // line 63
            yield "        ";
            yield $this->env->getFunction('render_block')->getCallable()($context["oBlock"], ["aPage" => ($context["aPage"] ?? null)]);
            yield "
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['oBlock'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        yield from [];
    }

    // line 67
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageTemplates(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        yield from [];
    }

    // line 110
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageJsFiles(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 111
        yield "    ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aJsFiles", [], "any", false, false, false, 111));
        foreach ($context['_seq'] as $context["_key"] => $context["sJsFile"]) {
            // line 112
            yield "        <script type=\"text/javascript\" src=\"";
            yield $this->env->getFilter('add_itop_version')->getCallable()($context["sJsFile"]);
            yield "\"></script>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['sJsFile'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        yield from [];
    }

    // line 116
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageJsInlineScripts(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 117
        yield "    ";
        if (( !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aJsInlineOnInit", [], "any", false, false, false, 117)) ||  !Twig\Extension\CoreExtension::testEmpty(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aJsInlineOnDomReady", [], "any", false, false, false, 117)))) {
            // line 118
            yield "        <script type=\"text/javascript\">
            ";
            // line 120
            yield "            \$(document).ready(function () {
                ";
            // line 121
            yield from $this->unwrap()->yieldBlock('iboPageJsInlineOnInit', $context, $blocks);
            // line 126
            yield "
                ";
            // line 127
            yield from $this->unwrap()->yieldBlock('iboPageJsInlineOnDomReady', $context, $blocks);
            // line 134
            yield "            });
        </script>
    ";
        }
        // line 137
        yield "    ";
        yield from $this->unwrap()->yieldBlock('iboPageJsInlineLive', $context, $blocks);
        yield from [];
    }

    // line 121
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageJsInlineOnInit(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 122
        yield "                    ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aJsInlineOnInit", [], "any", false, false, false, 122));
        foreach ($context['_seq'] as $context["_key"] => $context["sJsInline"]) {
            // line 123
            yield "                        ";
            yield $context["sJsInline"];
            yield "
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['sJsInline'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 125
        yield "                ";
        yield from [];
    }

    // line 127
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageJsInlineOnDomReady(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 128
        yield "                    setTimeout(function () {
                        ";
        // line 129
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aJsInlineOnDomReady", [], "any", false, false, false, 129));
        foreach ($context['_seq'] as $context["_key"] => $context["sJsInline"]) {
            // line 130
            yield "                        ";
            yield $context["sJsInline"];
            yield "
                        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['sJsInline'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 132
        yield "                    }, 50);
                ";
        yield from [];
    }

    // line 137
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboPageJsInlineLive(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 138
        yield "        ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "aJsInlineLive", [], "any", false, false, false, 138));
        foreach ($context['_seq'] as $context["_key"] => $context["sJsInline"]) {
            // line 139
            yield "            ";
            // line 140
            yield "            <script type=\"text/javascript\">
                ";
            // line 141
            yield $context["sJsInline"];
            yield "
            </script>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['sJsInline'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 144
        yield "    ";
        yield from [];
    }

    // line 147
    /**
     * @return iterable<null|scalar|\Stringable>
     */
    public function block_iboCapturedOutput(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 148
        yield "    ";
        yield CoreExtension::getAttribute($this->env, $this->source, ($context["aPage"] ?? null), "sCapturedOutput", [], "any", false, false, false, 148);
        yield "
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "pages/backoffice/webpage/layout.html.twig";
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
        return array (  570 => 148,  563 => 147,  558 => 144,  549 => 141,  546 => 140,  544 => 139,  539 => 138,  532 => 137,  526 => 132,  517 => 130,  513 => 129,  510 => 128,  503 => 127,  498 => 125,  489 => 123,  484 => 122,  477 => 121,  471 => 137,  466 => 134,  464 => 127,  461 => 126,  459 => 121,  456 => 120,  453 => 118,  450 => 117,  443 => 116,  431 => 112,  426 => 111,  419 => 110,  409 => 67,  397 => 63,  392 => 62,  385 => 61,  377 => 56,  374 => 55,  367 => 54,  362 => 50,  353 => 47,  350 => 46,  348 => 45,  343 => 44,  336 => 43,  331 => 41,  322 => 38,  319 => 37,  314 => 36,  312 => 35,  305 => 34,  300 => 32,  294 => 31,  290 => 30,  285 => 29,  278 => 28,  276 => 27,  271 => 26,  264 => 25,  259 => 9,  252 => 8,  245 => 150,  243 => 147,  240 => 146,  238 => 116,  235 => 115,  233 => 110,  229 => 108,  220 => 105,  215 => 103,  210 => 102,  206 => 101,  192 => 89,  188 => 84,  179 => 82,  175 => 81,  166 => 74,  163 => 69,  161 => 67,  158 => 66,  156 => 61,  153 => 60,  149 => 59,  146 => 54,  142 => 53,  138 => 51,  136 => 43,  133 => 42,  131 => 34,  128 => 33,  125 => 25,  123 => 22,  112 => 20,  107 => 19,  102 => 17,  96 => 15,  94 => 14,  89 => 13,  73 => 11,  70 => 10,  67 => 8,  63 => 6,  58 => 4,  55 => 3,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "pages/backoffice/webpage/layout.html.twig", "/var/www/html/itop/templates/pages/backoffice/webpage/layout.html.twig");
    }
}
