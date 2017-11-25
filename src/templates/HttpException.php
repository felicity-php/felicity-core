<html>
<head>
    <?php if ($this->getCode() === 404) : ?>
        <title>Page Not Found</title>
    <?php else : ?>
        <title>Internal Server Error</title>
    <?php endif; ?>
    <style type="text/css">
        /* Reset */
        html, body, div, span, applet, object, iframe,
        h1, h2, h3, h4, h5, h6, p, blockquote, pre,
        a, abbr, acronym, address, big, cite, code,
        del, dfn, em, img, ins, kbd, q, s, samp,
        small, strike, strong, sub, sup, tt, var,
        b, u, i, center,
        dl, dt, dd, ol, ul, li,
        fieldset, form, label, legend,
        table, caption, tbody, tfoot, thead, tr, th, td,
        article, aside, canvas, details, embed,
        figure, figcaption, footer, header, hgroup,
        menu, nav, output, ruby, section, summary,
        time, mark, audio, video {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
        }
        /* HTML5 display-role reset for older browsers */
        article, aside, details, figcaption, figure,
        footer, header, hgroup, menu, nav, section {
            display: block;
        }
        body {
            line-height: 1;
        }
        ol, ul {
            list-style: none;
        }
        blockquote, q {
            quotes: none;
        }
        blockquote:before, blockquote:after,
        q:before, q:after {
            content: '';
            content: none;
        }
        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        .body {
            background-color: #efefef;
        }

        .error {
            background-color: #fff;
            display: block;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 13px;
            left: 50%;
            line-height: 1.2em;
            max-width: 76vw;
            padding: 20px;
            position: fixed;
            top: 50%;
            text-align: center;
            transform: translate(-50%, -50%);
            width: 420px;
        }

        .error__title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="body">
<main class="error">
    <?php if ($this->getCode() === 404) : ?>
        <h1 class="error__title">Page Not&nbsp;Found</h1>
        <p class="error__body">
            We couldn&rsquo;t find the requested&nbsp;URL.
        </p>
    <?php else : ?>
        <h1 class="error__title">Internal Server&nbsp;Error</h1>
        <p class="error__body">
            Something went wrong and we&rsquo;re currently unable to process the page&nbsp;request.
        </p>
    <?php endif; ?>
</main>
</body>
</html>
