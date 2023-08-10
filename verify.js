const args = process.argv.slice(2);

import puppeteer from "puppeteer";
import * as cheerio from "cheerio";

const handle = args[0];
let channel = args[1];

if (channel && channel.slice(-1) != "/") {
    channel = channel + "/";
}

const params = { handle, channel };

async function verifyChannel(params) {
    const { handle, channel } = params;
    let id = "";
    let audiuslinks = [];
    try {
        const browser = await puppeteer.launch({
            // headless: "new",
        });
        const page = await browser.newPage();
        const url = `${decodeURIComponent(channel)}about`;
        await page.goto(url, {
            waitUntil: "networkidle2",
        });
        const $ = cheerio.load(await page.content());
        $("a").each((i, link) => {
            const loadedlink = $(link);
            if (loadedlink.text().includes("Audius"))
                audiuslinks.push(loadedlink.attr("href"));
        });
        $('link[rel="alternate"]').each((i, link) => {
            const href = $(link).attr("href");
            if (href.includes("videos.xml?channel_id")) {
                id = href.split("=")[1];
            }
        });
        if (audiuslinks.length) {
            const link = audiuslinks[0];
            const page = await browser.newPage();
            await page.goto(link, {
                waitUntil: "networkidle2",
            });
            browser.close();
            if (`https://audius.co/${handle}` == page.url()) {
                console.log(JSON.stringify({ id: id }));
                process.exit(0);
            } else {
                console.log("Incorrect Audius link in profile");
                process.exit(1);
            }
        } else {
            browser.close();
            console.log("No Audius link in profile");
            process.exit(1);
        }
    } catch (error) {
        console.log(error);
        process.exit(1);
    }
}

verifyChannel(params);
