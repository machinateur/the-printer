/*
 * MIT License
 *
 * Copyright (c) 2020-2024 machinateur
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

(async function () {
    console.info('the-printer');
    console.info('> The printer, a prototype. Create PDF documents and images from HTML content using puppeteer and headless-chrome.');
    console.info('> Copyright (c) 2020-2024 machinateur');

    // Set up 'minimist'...

    const minimist = require('minimist');

    /**
     * @type {{_:[]}&{debug?:boolean,'connect-to'?:string,'no-script'?:boolean}}
     */
    const args = minimist(process.argv.slice(2));

    // Set up args and env...

    const debugMode = !!args['debug'] || !!process.env['APP_DEBUG'] || false;
    const connectTo = args['connect-to'] || process.env['APP_REMOTE'] || null;
    const noScripts = !(!!args['script'] || !!process.env['APP_ENABLE_JAVASCRIPT'] || false);
    let port = Number(args['port'] || process.env['APP_PORT']);
    if (isNaN(port)) {
        port = 3000;
    }

    // Set up 'puppeteer'...

    /**
     * @type {import('puppeteer').PuppeteerNode}
     */
    const puppeteer = require('puppeteer');

    const browser = await (
        /**
         * Connect or launch a browser instance.
         *
         * @return {Promise<Browser>}
         */
        function () {
            if (null !== connectTo) {
                // Handle `ws:` (websocket) protocol.
                if (connectTo.startsWith('ws:')) {
                    /**
                     * @type {Browser}
                     * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.connect.md
                     * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.connectoptions.md
                     * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.browserconnectoptions.md
                     */
                    return puppeteer.connect({
                        browserWSEndpoint: connectTo,
                        ignoreHTTPSErrors: debugMode,
                    });
                }

                /**
                 * @type {Browser}
                 * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.connect.md
                 * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.connectoptions.md
                 * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.browserconnectoptions.md
                 */
                return puppeteer.connect({
                    browserURL: connectTo,
                    ignoreHTTPSErrors: debugMode,
                });
            }

            /**
             * @type {Browser}
             * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.launch.md
             * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.puppeteerlaunchoptions.md
             * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.launchoptions.md
             * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.browserlaunchargumentoptions.md
             * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.browserconnectoptions.md
             */
            return puppeteer.launch({
                dumpio: debugMode,
                headless: true,
                ignoreHTTPSErrors: debugMode,
            });
        }
    )();

    process.on('exit',
        /**
         * @param {number} code The exit code.
         */
        async function (code) {
            console.info(`App exit code '${code}'.`);

            await browser.close();
        }
    );

    // Set up 'express'...

    const express = require('express');

    /**
     * @type {import('express').Express}
     */
    const app = express();

    // Set up type definition...

    /**
     * @typedef {object} DocumentConfiguration
     * @property {number|null} scale
     * @property {boolean} displayContentOnly
     * @property {boolean} displayBackgroundGraphic
     * @property {boolean} displayTransparent
     * @property {object|null} template
     * @property {string|null} template.header
     * @property {string|null} template.footer
     * @property {string} pageOrientation
     * @property {string|null} pageFormat
     * @property {string|number|undefined} pageWidth
     * @property {string|number|undefined} pageHeight
     * @property {string} pageRange
     * @property {boolean} pageOverride
     * @property {object|null} margin
     * @property {string|number|undefined} margin.top
     * @property {string|number|undefined} margin.right
     * @property {string|number|undefined} margin.bottom
     * @property {string|number|undefined} margin.left
     */

    /**
     * @typedef {object} ImageConfiguration
     * @property {string|null} type
     * @property {number|null} quality
     * @property {number|null} scale
     * @property {object|null} area
     * @property {number} area.x
     * @property {number} area.y
     * @property {number} area.width
     * @property {number} area.height
     * @property {boolean} optimize
     * @property {boolean} captureViewportOnly
     * @property {boolean} captureSurface
     * @property {boolean} capturePage
     * @property {boolean} displayTransparent
     */

    // Set up function...

    /**
     * Create a new page in the browser. Wait for `networkidle0` event.
     *
     * @param {string} content The page content.
     * @param {number|null} timeout The timeout.
     * @return {Promise<Page>}
     */
    async function newPage(content, timeout = null) {
        const page = await browser.newPage();
        page.setJavaScriptEnabled(!noScripts);

        /**
         * @type {import('puppeteer').WaitForOptions}
         * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.waitforoptions.md
         */
        const optionObject = {};
        if (null !== timeout) {
            optionObject.timeout = timeout;
        } else {
            optionObject.timeout = 0;
        }
        optionObject.waitUntil = 'networkidle0';

        await page.setContent(content, optionObject);

        return page;
    }

    /**
     * Get a pdf document as stream from a content string and a configuration object.
     *
     * @todo implement path, timeout, waitForFonts in newer version
     *
     * @param {string} content The content string.
     * @param {DocumentConfiguration} configuration The document configuration object.
     * @return {Promise<Buffer>}
     */
    async function getDocumentStream(content, configuration) {
        const page = await newPage(content);

        /**
         * @type {import('puppeteer').PDFOptions}
         * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.pdfoptions.md
         * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.pdfmargin.md
         * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.paperformat.md
         */
        const optionObject = {};
        optionObject.path = null;
        if (null !== configuration.scale) {
            optionObject.scale = configuration.scale;
        }
        optionObject.displayHeaderFooter = !configuration.displayContentOnly;
        optionObject.printBackground = configuration.displayBackgroundGraphic;
        optionObject.omitBackground = configuration.displayTransparent;
        if (null !== configuration.template) {
            optionObject.headerTemplate = configuration.template.header;
            optionObject.footerTemplate = configuration.template.footer;
        }
        optionObject.landscape = ('landscape' === configuration.pageOrientation);
        optionObject.pageRanges = configuration.pageRange;
        if (null !== configuration.pageFormat) {
            configuration.pageFormat = configuration.pageFormat.toUpperCase();

            if (!['LETTER', 'LEGAL', 'TABLOID', 'LEDGER', 'A0', 'A1', 'A2', 'A3', 'A4', 'A5', 'A6'].includes(configuration.pageFormat)) {
                configuration.pageFormat = 'A4';
            }

            // noinspection JSValidateTypes
            optionObject.format = configuration.pageFormat;
        } else {
            optionObject.format = configuration.pageFormat = 'A4';
        }
        optionObject.width = configuration.pageWidth;
        optionObject.height = configuration.pageHeight;
        if (null !== configuration.margin) {
            optionObject.margin = configuration.margin;
        } else {
            optionObject.margin = undefined;
        }
        optionObject.preferCSSPageSize = configuration.pageOverride;

        /**
         * @type {Buffer}
         * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.page.pdf.md
         */
        const stream = await page.pdf(optionObject);

        await page.close();

        return stream;
    }

    /**
     * Get an image as stream from a content string and a configuration object.
     *
     * @param {string} content
     * @param {ImageConfiguration} configuration
     * @return {Promise<Buffer>}
     */
    async function getImageStream(content, configuration) {
        const page = await newPage(content);

        /**
         * @type {import('puppeteer').ScreenshotOptions}
         * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.screenshotoptions.md
         * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.screenshotclip.md
         */
        const optionObject = {};
        optionObject.path = null;
        optionObject.encoding = 'binary';
        if (null !== configuration.type) {
            configuration.type = configuration.type.toLowerCase();

            if (!['png', 'jpeg', 'webp'].includes(configuration.type)) {
                if (null !== configuration.quality) {
                    configuration.type = 'jpeg';
                } else {
                    configuration.type = 'png';
                }
            } else if ('jpg' === configuration.type) {
                configuration.type = 'jpeg';
            }

            // noinspection JSValidateTypes
            optionObject.type = configuration.type;

            if ('png' !== configuration.type && null !== configuration.quality) {
                optionObject.quality = Math.min(Math.max(configuration.quality, 0), 100);
            }
        } else {
            optionObject.type = configuration.type = 'png';
        }
        if (null !== configuration.area) {
            optionObject.clip = configuration.area;

            if (null !== configuration.scale) {
                optionObject.clip.scale = configuration.scale;
            }
        }
        optionObject.optimizeForSpeed = configuration.optimize;
        optionObject.captureBeyondViewport = !configuration.captureViewportOnly;
        optionObject.fromSurface = configuration.captureSurface;
        optionObject.fullPage = configuration.capturePage;
        optionObject.omitBackground = configuration.displayTransparent;

        /**
         * @type {Buffer}
         * @see https://github.com/puppeteer/puppeteer/blob/main/docs/api/puppeteer.page.screenshot_1.md
         */
        const stream = await page.screenshot(optionObject);

        await page.close();

        return stream;
    }

    /**
     * Validate the body object.
     *
     * @param {{configuration:object,content:string,time:number}} body The body object.
     * @return {boolean}
     */
    function validateBodyObject(body) {
        return (body !== null)
            && 'object' === typeof body['configuration']
            && 'string' === typeof body['content']
            && 'number' === typeof body['time'];
    }

    // Set up middleware...

    app.use(
        express.json({
            strict: true,
        })
    );

    /**
     * Custom handler to validate the request body format. Only validate first-level object structure.
     *
     * @type {import('express').Handler}
     */
    const validateBody = (
        /**
         @param {import('express').Request} request The request object.
         @param {import('express').Response} response The response object.
         @param {import('express').NextFunction} next The next handler.
         @return {void}
         */
        function (request, response, next) {
            /**
             * @type {object}
             */
            const body = request.body;

            if (!validateBodyObject(body)) {
                const error = new Error('Invalid request body format');
                error.statusCode = 400;

                next(error);
                return;
            }

            next();
        }
    );

    // Set up app...

    app.post('/document', validateBody,
        /**
         * @param {import('express').Request} request The request object.
         * @param {import('express').Response} response The response object.
         * @return {void}
         */
        async function (request, response) {
            /**
             * @type {object}
             */
            const body = request.body;

            /**
             * @type {DocumentConfiguration}
             */
            const configuration = body.configuration;
            /**
             * @type {string}
             */
            const content = body.content;
            /**
             * @type {number}
             */
            const time = body.time;

            if (debugMode) {
                console.debug(`Hit image endpoint at ${time}.`, configuration);
                console.debug('With Content:', content);
            }

            const stream = await getDocumentStream(content, configuration);

            response.type('application/pdf');
            response.end(stream, 'binary');
        }
    );

    app.post('/image', validateBody,
        /**
         * @param {import('express').Request} request The request object.
         * @param {import('express').Response} response The response object.
         * @return {void}
         */
        async function (request, response) {
            /**
             * @type {object}
             */
            const body = request.body;

            /**
             * @type {ImageConfiguration}
             */
            const configuration = body.configuration;
            /**
             * @type {string}
             */
            const content = body.content;
            /**
             * @type {number}
             */
            const time = body.time;

            if (debugMode) {
                console.debug(`Hit image endpoint at ${time}.`, configuration);
                console.debug('With Content:', content);
            }

            const stream = await getImageStream(content, configuration);

            response.type(`image/${configuration.type ?? 'png'}`);
            response.end(stream, 'binary');
        }
    );

    // Set up error handler...

    // noinspection JSUnusedLocalSymbols
    app.use(
        /**
         * @param {Error&{statusCode?:number}} error
         * @param {import('express').Request} request
         * @param {import('express').Response} response
         * @param {import('express').NextFunction} next
         */
        function (error, request, response, next) {
            console.error(error.message, error);

            if ('undefined' === typeof error.statusCode) {
                error.statusCode = 500;
            }

            const body = {
                statusCode: error.statusCode,
                message: error.message,
                stack: error.stack,
            };

            if (!debugMode) {
                delete body.stack;
            }

            response.status(error.statusCode);
            response.json(body);
        }
    );

    // Start app...

    app.listen(port, function () {
        console.info(`App at 'http://127.0.0.1:${port}'.`);
    });
})();
