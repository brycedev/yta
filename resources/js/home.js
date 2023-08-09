import "./bootstrap";

import {
    DiscoveryNodeSelector,
    EntityManager,
    sdk,
    stagingConfig,
} from "@audius/sdk";

const discoveryNodeSelector = new DiscoveryNodeSelector({
    initialSelectedNode: "https://discoveryprovider.staging.audius.co/",
});
let appName = "YoutubeToAudius_Staging";
if (import.meta.env.VITE_AUDIUS_ENV === "production") {
    appName = "YoutubeToAudius";
}
let sdkConfig = {
    appName: appName,
    apiKey: import.meta.env.VITE_AUDIUS_API_KEY,
    apiSecret: "",
};
if (import.meta.env.VITE_AUDIUS_ENV === "staging") {
    sdkConfig.services = {
        discoveryNodeSelector,
        entityManager: new EntityManager({
            discoveryNodeSelector,
            web3ProviderUrl: stagingConfig.web3ProviderUrl,
            contractAddress: stagingConfig.entityManagerContractAddress,
            identityServiceUrl: stagingConfig.identityServiceUrl,
        }),
    };
}
const audiusSdk = sdk(sdkConfig);
const oauthConfig = {
    successCallback: (res) => {
        window.axios
            .post("/auth/login", {
                email: res.email,
                audius_handle: res.handle,
                audius_id: res.userId,
                avatar_url: res.profilePicture["_1000x1000"],
            })
            .then((response) => {
                window.location.href = "/dashboard";
            });
    },
};
if (import.meta.env.VITE_AUDIUS_ENV === "staging") {
    oauthConfig.env = "staging";
}
audiusSdk.oauth.init(oauthConfig);
audiusSdk.oauth.renderButton({
    element: document.getElementById("audius-login-button"),
    scope: "write",
});
