import { c as collectBodyString, g as getSmithyContext, n as normalizeProvider, r as resolveAwsSdkSigV4Config, a as resolveEndpoint, b as customEndpointFunctions, d as awsEndpointFunctions, f as fromBase64, t as toBase64, A as AwsSdkSigV4Signer, N as NoOpLogger, p as parseUrl, e as fromUtf8, h as toUtf8, i as calculateBodyLength, j as defaultUserAgent, D as DEFAULT_MAX_ATTEMPTS, k as invalidProvider, F as FetchHttpHandler, l as DEFAULT_RETRY_MODE, m as Sha256, s as streamCollector, o as DEFAULT_USE_DUALSTACK_ENDPOINT, q as DEFAULT_USE_FIPS_ENDPOINT, u as resolveDefaultsModeConfig, v as loadConfigsForDefaultMode, w as getDefaultExtensionConfiguration, x as getHttpHandlerExtensionConfiguration, y as resolveAwsRegionExtensionConfiguration, z as resolveDefaultRuntimeConfig, B as resolveHttpHandlerRuntimeConfig, C as getAwsRegionExtensionConfiguration, E as Client, G as resolveUserAgentConfig, H as resolveRetryConfig, I as resolveRegionConfig, J as resolveEndpointConfig, K as getUserAgentPlugin, L as getRetryPlugin, M as getContentLengthPlugin, O as getHostHeaderPlugin, P as getLoggerPlugin, Q as getRecursionDetectionPlugin, R as getHttpAuthSchemeEndpointRuleSetPlugin, T as DefaultIdentityProviderConfig, U as getHttpSigningPlugin, V as resolveHostHeaderConfig, W as ServiceException, X as decorateServiceException, Y as take, Z as withBaseException, _ as HttpRequest, $ as expectString, a0 as expectNonNull, a1 as parseEpochTimestamp, a2 as expectNumber, a3 as Command, a4 as getSerdePlugin, a5 as getEndpointPlugin } from "./s3-BB0I09dN-2024_9_14_11_28.js";
import "./index-CKtHrVPI-2024_9_14_11_28.js";
const _json = (obj) => {
  if (obj == null) {
    return {};
  }
  if (Array.isArray(obj)) {
    return obj.filter((_) => _ != null).map(_json);
  }
  if (typeof obj === "object") {
    const target = {};
    for (const key of Object.keys(obj)) {
      if (obj[key] == null) {
        continue;
      }
      target[key] = _json(obj[key]);
    }
    return target;
  }
  return obj;
};
class NoAuthSigner {
  async sign(httpRequest, identity, signingProperties) {
    return httpRequest;
  }
}
const parseJsonBody = (streamBody, context) => collectBodyString(streamBody, context).then((encoded) => {
  if (encoded.length) {
    try {
      return JSON.parse(encoded);
    } catch (e2) {
      if ((e2 == null ? void 0 : e2.name) === "SyntaxError") {
        Object.defineProperty(e2, "$responseBodyText", {
          value: encoded
        });
      }
      throw e2;
    }
  }
  return {};
});
const parseJsonErrorBody = async (errorBody, context) => {
  var _a;
  const value = await parseJsonBody(errorBody, context);
  value.message = (_a = value.message) != null ? _a : value.Message;
  return value;
};
const loadRestJsonErrorCode = (output, data) => {
  const findKey = (object, key) => Object.keys(object).find((k2) => k2.toLowerCase() === key.toLowerCase());
  const sanitizeErrorCode = (rawValue) => {
    let cleanValue = rawValue;
    if (typeof cleanValue === "number") {
      cleanValue = cleanValue.toString();
    }
    if (cleanValue.indexOf(",") >= 0) {
      cleanValue = cleanValue.split(",")[0];
    }
    if (cleanValue.indexOf(":") >= 0) {
      cleanValue = cleanValue.split(":")[0];
    }
    if (cleanValue.indexOf("#") >= 0) {
      cleanValue = cleanValue.split("#")[1];
    }
    return cleanValue;
  };
  const headerKey = findKey(output.headers, "x-amzn-errortype");
  if (headerKey !== void 0) {
    return sanitizeErrorCode(output.headers[headerKey]);
  }
  if (data.code !== void 0) {
    return sanitizeErrorCode(data.code);
  }
  if (data["__type"] !== void 0) {
    return sanitizeErrorCode(data["__type"]);
  }
};
const defaultCognitoIdentityHttpAuthSchemeParametersProvider = async (config, context, input) => {
  return {
    operation: getSmithyContext(context).operation,
    region: await normalizeProvider(config.region)() || (() => {
      throw new Error("expected `region` to be configured for `aws.auth#sigv4`");
    })()
  };
};
function createAwsAuthSigv4HttpAuthOption(authParameters) {
  return {
    schemeId: "aws.auth#sigv4",
    signingProperties: {
      name: "cognito-identity",
      region: authParameters.region
    },
    propertiesExtractor: (config, context) => ({
      signingProperties: {
        config,
        context
      }
    })
  };
}
function createSmithyApiNoAuthHttpAuthOption(authParameters) {
  return {
    schemeId: "smithy.api#noAuth"
  };
}
const defaultCognitoIdentityHttpAuthSchemeProvider = (authParameters) => {
  const options = [];
  switch (authParameters.operation) {
    case "GetCredentialsForIdentity": {
      options.push(createSmithyApiNoAuthHttpAuthOption());
      break;
    }
    case "GetId": {
      options.push(createSmithyApiNoAuthHttpAuthOption());
      break;
    }
    case "GetOpenIdToken": {
      options.push(createSmithyApiNoAuthHttpAuthOption());
      break;
    }
    case "UnlinkIdentity": {
      options.push(createSmithyApiNoAuthHttpAuthOption());
      break;
    }
    default: {
      options.push(createAwsAuthSigv4HttpAuthOption(authParameters));
    }
  }
  return options;
};
const resolveHttpAuthSchemeConfig = (config) => {
  const config_0 = resolveAwsSdkSigV4Config(config);
  return {
    ...config_0
  };
};
const resolveClientEndpointParameters = (options) => {
  var _a, _b;
  return {
    ...options,
    useDualstackEndpoint: (_a = options.useDualstackEndpoint) != null ? _a : false,
    useFipsEndpoint: (_b = options.useFipsEndpoint) != null ? _b : false,
    defaultSigningName: "cognito-identity"
  };
};
const commonParams = {
  UseFIPS: { type: "builtInParams", name: "useFipsEndpoint" },
  Endpoint: { type: "builtInParams", name: "endpoint" },
  Region: { type: "builtInParams", name: "region" },
  UseDualStack: { type: "builtInParams", name: "useDualstackEndpoint" }
};
const name = "@aws-sdk/client-cognito-identity";
const description = "AWS SDK for JavaScript Cognito Identity Client for Node.js, Browser and React Native";
const version = "3.637.0";
const scripts = {
  build: "concurrently 'yarn:build:cjs' 'yarn:build:es' 'yarn:build:types'",
  "build:cjs": "node ../../scripts/compilation/inline client-cognito-identity",
  "build:es": "tsc -p tsconfig.es.json",
  "build:include:deps": "lerna run --scope $npm_package_name --include-dependencies build",
  "build:types": "tsc -p tsconfig.types.json",
  "build:types:downlevel": "downlevel-dts dist-types dist-types/ts3.4",
  clean: "rimraf ./dist-* && rimraf *.tsbuildinfo",
  "extract:docs": "api-extractor run --local",
  "generate:client": "node ../../scripts/generate-clients/single-service --solo cognito-identity",
  "test:e2e": "ts-mocha test/**/*.ispec.ts && karma start karma.conf.js"
};
const main = "./dist-cjs/index.js";
const types = "./dist-types/index.d.ts";
const module = "./dist-es/index.js";
const sideEffects = false;
const dependencies = {
  "@aws-crypto/sha256-browser": "5.2.0",
  "@aws-crypto/sha256-js": "5.2.0",
  "@aws-sdk/client-sso-oidc": "3.637.0",
  "@aws-sdk/client-sts": "3.637.0",
  "@aws-sdk/core": "3.635.0",
  "@aws-sdk/credential-provider-node": "3.637.0",
  "@aws-sdk/middleware-host-header": "3.620.0",
  "@aws-sdk/middleware-logger": "3.609.0",
  "@aws-sdk/middleware-recursion-detection": "3.620.0",
  "@aws-sdk/middleware-user-agent": "3.637.0",
  "@aws-sdk/region-config-resolver": "3.614.0",
  "@aws-sdk/types": "3.609.0",
  "@aws-sdk/util-endpoints": "3.637.0",
  "@aws-sdk/util-user-agent-browser": "3.609.0",
  "@aws-sdk/util-user-agent-node": "3.614.0",
  "@smithy/config-resolver": "^3.0.5",
  "@smithy/core": "^2.4.0",
  "@smithy/fetch-http-handler": "^3.2.4",
  "@smithy/hash-node": "^3.0.3",
  "@smithy/invalid-dependency": "^3.0.3",
  "@smithy/middleware-content-length": "^3.0.5",
  "@smithy/middleware-endpoint": "^3.1.0",
  "@smithy/middleware-retry": "^3.0.15",
  "@smithy/middleware-serde": "^3.0.3",
  "@smithy/middleware-stack": "^3.0.3",
  "@smithy/node-config-provider": "^3.1.4",
  "@smithy/node-http-handler": "^3.1.4",
  "@smithy/protocol-http": "^4.1.0",
  "@smithy/smithy-client": "^3.2.0",
  "@smithy/types": "^3.3.0",
  "@smithy/url-parser": "^3.0.3",
  "@smithy/util-base64": "^3.0.0",
  "@smithy/util-body-length-browser": "^3.0.0",
  "@smithy/util-body-length-node": "^3.0.0",
  "@smithy/util-defaults-mode-browser": "^3.0.15",
  "@smithy/util-defaults-mode-node": "^3.0.15",
  "@smithy/util-endpoints": "^2.0.5",
  "@smithy/util-middleware": "^3.0.3",
  "@smithy/util-retry": "^3.0.3",
  "@smithy/util-utf8": "^3.0.0",
  tslib: "^2.6.2"
};
const devDependencies = {
  "@aws-sdk/client-iam": "3.637.0",
  "@tsconfig/node16": "16.1.3",
  "@types/chai": "^4.2.11",
  "@types/mocha": "^8.0.4",
  "@types/node": "^16.18.96",
  concurrently: "7.0.0",
  "downlevel-dts": "0.10.1",
  rimraf: "3.0.2",
  typescript: "~4.9.5"
};
const engines = {
  node: ">=16.0.0"
};
const typesVersions = {
  "<4.0": {
    "dist-types/*": [
      "dist-types/ts3.4/*"
    ]
  }
};
const files = [
  "dist-*/**"
];
const author = {
  name: "AWS SDK for JavaScript Team",
  url: "https://aws.amazon.com/javascript/"
};
const license = "Apache-2.0";
const browser = {
  "./dist-es/runtimeConfig": "./dist-es/runtimeConfig.browser"
};
const homepage = "https://github.com/aws/aws-sdk-js-v3/tree/main/clients/client-cognito-identity";
const repository = {
  type: "git",
  url: "https://github.com/aws/aws-sdk-js-v3.git",
  directory: "clients/client-cognito-identity"
};
const packageInfo = {
  name,
  description,
  version,
  scripts,
  main,
  types,
  module,
  sideEffects,
  dependencies,
  devDependencies,
  engines,
  typesVersions,
  files,
  author,
  license,
  browser,
  "react-native": {
    "./dist-es/runtimeConfig": "./dist-es/runtimeConfig.native"
  },
  homepage,
  repository
};
const s = "required", t = "fn", u = "argv", v = "ref";
const a = true, b = "isSet", c = "booleanEquals", d = "error", e = "endpoint", f = "tree", g = "PartitionResult", h = { [s]: false, "type": "String" }, i = { [s]: true, "default": false, "type": "Boolean" }, j = { [v]: "Endpoint" }, k = { [t]: c, [u]: [{ [v]: "UseFIPS" }, true] }, l = { [t]: c, [u]: [{ [v]: "UseDualStack" }, true] }, m = {}, n = { [t]: "getAttr", [u]: [{ [v]: g }, "supportsFIPS"] }, o = { [t]: c, [u]: [true, { [t]: "getAttr", [u]: [{ [v]: g }, "supportsDualStack"] }] }, p = [k], q = [l], r = [{ [v]: "Region" }];
const _data = { version: "1.0", parameters: { Region: h, UseDualStack: i, UseFIPS: i, Endpoint: h }, rules: [{ conditions: [{ [t]: b, [u]: [j] }], rules: [{ conditions: p, error: "Invalid Configuration: FIPS and custom endpoint are not supported", type: d }, { conditions: q, error: "Invalid Configuration: Dualstack and custom endpoint are not supported", type: d }, { endpoint: { url: j, properties: m, headers: m }, type: e }], type: f }, { conditions: [{ [t]: b, [u]: r }], rules: [{ conditions: [{ [t]: "aws.partition", [u]: r, assign: g }], rules: [{ conditions: [k, l], rules: [{ conditions: [{ [t]: c, [u]: [a, n] }, o], rules: [{ endpoint: { url: "https://cognito-identity-fips.{Region}.{PartitionResult#dualStackDnsSuffix}", properties: m, headers: m }, type: e }], type: f }, { error: "FIPS and DualStack are enabled, but this partition does not support one or both", type: d }], type: f }, { conditions: p, rules: [{ conditions: [{ [t]: c, [u]: [n, a] }], rules: [{ endpoint: { url: "https://cognito-identity-fips.{Region}.{PartitionResult#dnsSuffix}", properties: m, headers: m }, type: e }], type: f }, { error: "FIPS is enabled but this partition does not support FIPS", type: d }], type: f }, { conditions: q, rules: [{ conditions: [o], rules: [{ endpoint: { url: "https://cognito-identity.{Region}.{PartitionResult#dualStackDnsSuffix}", properties: m, headers: m }, type: e }], type: f }, { error: "DualStack is enabled but this partition does not support DualStack", type: d }], type: f }, { endpoint: { url: "https://cognito-identity.{Region}.{PartitionResult#dnsSuffix}", properties: m, headers: m }, type: e }], type: f }], type: f }, { error: "Invalid Configuration: Missing Region", type: d }] };
const ruleSet = _data;
const defaultEndpointResolver = (endpointParams, context = {}) => {
  return resolveEndpoint(ruleSet, {
    endpointParams,
    logger: context.logger
  });
};
customEndpointFunctions.aws = awsEndpointFunctions;
const getRuntimeConfig$1 = (config) => {
  var _a, _b, _c, _d, _e, _f, _g, _h, _i, _j, _k, _l;
  return {
    apiVersion: "2014-06-30",
    base64Decoder: (_a = config == null ? void 0 : config.base64Decoder) != null ? _a : fromBase64,
    base64Encoder: (_b = config == null ? void 0 : config.base64Encoder) != null ? _b : toBase64,
    disableHostPrefix: (_c = config == null ? void 0 : config.disableHostPrefix) != null ? _c : false,
    endpointProvider: (_d = config == null ? void 0 : config.endpointProvider) != null ? _d : defaultEndpointResolver,
    extensions: (_e = config == null ? void 0 : config.extensions) != null ? _e : [],
    httpAuthSchemeProvider: (_f = config == null ? void 0 : config.httpAuthSchemeProvider) != null ? _f : defaultCognitoIdentityHttpAuthSchemeProvider,
    httpAuthSchemes: (_g = config == null ? void 0 : config.httpAuthSchemes) != null ? _g : [
      {
        schemeId: "aws.auth#sigv4",
        identityProvider: (ipc) => ipc.getIdentityProvider("aws.auth#sigv4"),
        signer: new AwsSdkSigV4Signer()
      },
      {
        schemeId: "smithy.api#noAuth",
        identityProvider: (ipc) => ipc.getIdentityProvider("smithy.api#noAuth") || (async () => ({})),
        signer: new NoAuthSigner()
      }
    ],
    logger: (_h = config == null ? void 0 : config.logger) != null ? _h : new NoOpLogger(),
    serviceId: (_i = config == null ? void 0 : config.serviceId) != null ? _i : "Cognito Identity",
    urlParser: (_j = config == null ? void 0 : config.urlParser) != null ? _j : parseUrl,
    utf8Decoder: (_k = config == null ? void 0 : config.utf8Decoder) != null ? _k : fromUtf8,
    utf8Encoder: (_l = config == null ? void 0 : config.utf8Encoder) != null ? _l : toUtf8
  };
};
const getRuntimeConfig = (config) => {
  var _a, _b, _c, _d, _e, _f, _g, _h, _i, _j, _k;
  const defaultsMode = resolveDefaultsModeConfig(config);
  const defaultConfigProvider = () => defaultsMode().then(loadConfigsForDefaultMode);
  const clientSharedValues = getRuntimeConfig$1(config);
  return {
    ...clientSharedValues,
    ...config,
    runtime: "browser",
    defaultsMode,
    bodyLengthChecker: (_a = config == null ? void 0 : config.bodyLengthChecker) != null ? _a : calculateBodyLength,
    credentialDefaultProvider: (_b = config == null ? void 0 : config.credentialDefaultProvider) != null ? _b : (_) => () => Promise.reject(new Error("Credential is missing")),
    defaultUserAgentProvider: (_c = config == null ? void 0 : config.defaultUserAgentProvider) != null ? _c : defaultUserAgent({ serviceId: clientSharedValues.serviceId, clientVersion: packageInfo.version }),
    maxAttempts: (_d = config == null ? void 0 : config.maxAttempts) != null ? _d : DEFAULT_MAX_ATTEMPTS,
    region: (_e = config == null ? void 0 : config.region) != null ? _e : invalidProvider("Region is missing"),
    requestHandler: FetchHttpHandler.create((_f = config == null ? void 0 : config.requestHandler) != null ? _f : defaultConfigProvider),
    retryMode: (_g = config == null ? void 0 : config.retryMode) != null ? _g : async () => (await defaultConfigProvider()).retryMode || DEFAULT_RETRY_MODE,
    sha256: (_h = config == null ? void 0 : config.sha256) != null ? _h : Sha256,
    streamCollector: (_i = config == null ? void 0 : config.streamCollector) != null ? _i : streamCollector,
    useDualstackEndpoint: (_j = config == null ? void 0 : config.useDualstackEndpoint) != null ? _j : () => Promise.resolve(DEFAULT_USE_DUALSTACK_ENDPOINT),
    useFipsEndpoint: (_k = config == null ? void 0 : config.useFipsEndpoint) != null ? _k : () => Promise.resolve(DEFAULT_USE_FIPS_ENDPOINT)
  };
};
const getHttpAuthExtensionConfiguration = (runtimeConfig) => {
  const _httpAuthSchemes = runtimeConfig.httpAuthSchemes;
  let _httpAuthSchemeProvider = runtimeConfig.httpAuthSchemeProvider;
  let _credentials = runtimeConfig.credentials;
  return {
    setHttpAuthScheme(httpAuthScheme) {
      const index = _httpAuthSchemes.findIndex((scheme) => scheme.schemeId === httpAuthScheme.schemeId);
      if (index === -1) {
        _httpAuthSchemes.push(httpAuthScheme);
      } else {
        _httpAuthSchemes.splice(index, 1, httpAuthScheme);
      }
    },
    httpAuthSchemes() {
      return _httpAuthSchemes;
    },
    setHttpAuthSchemeProvider(httpAuthSchemeProvider) {
      _httpAuthSchemeProvider = httpAuthSchemeProvider;
    },
    httpAuthSchemeProvider() {
      return _httpAuthSchemeProvider;
    },
    setCredentials(credentials) {
      _credentials = credentials;
    },
    credentials() {
      return _credentials;
    }
  };
};
const resolveHttpAuthRuntimeConfig = (config) => {
  return {
    httpAuthSchemes: config.httpAuthSchemes(),
    httpAuthSchemeProvider: config.httpAuthSchemeProvider(),
    credentials: config.credentials()
  };
};
const asPartial = (t2) => t2;
const resolveRuntimeExtensions = (runtimeConfig, extensions) => {
  const extensionConfiguration = {
    ...asPartial(getAwsRegionExtensionConfiguration(runtimeConfig)),
    ...asPartial(getDefaultExtensionConfiguration(runtimeConfig)),
    ...asPartial(getHttpHandlerExtensionConfiguration(runtimeConfig)),
    ...asPartial(getHttpAuthExtensionConfiguration(runtimeConfig))
  };
  extensions.forEach((extension) => extension.configure(extensionConfiguration));
  return {
    ...runtimeConfig,
    ...resolveAwsRegionExtensionConfiguration(extensionConfiguration),
    ...resolveDefaultRuntimeConfig(extensionConfiguration),
    ...resolveHttpHandlerRuntimeConfig(extensionConfiguration),
    ...resolveHttpAuthRuntimeConfig(extensionConfiguration)
  };
};
class CognitoIdentityClient extends Client {
  constructor(...[configuration]) {
    const _config_0 = getRuntimeConfig(configuration || {});
    const _config_1 = resolveClientEndpointParameters(_config_0);
    const _config_2 = resolveUserAgentConfig(_config_1);
    const _config_3 = resolveRetryConfig(_config_2);
    const _config_4 = resolveRegionConfig(_config_3);
    const _config_5 = resolveHostHeaderConfig(_config_4);
    const _config_6 = resolveEndpointConfig(_config_5);
    const _config_7 = resolveHttpAuthSchemeConfig(_config_6);
    const _config_8 = resolveRuntimeExtensions(_config_7, (configuration == null ? void 0 : configuration.extensions) || []);
    super(_config_8);
    this.config = _config_8;
    this.middlewareStack.use(getUserAgentPlugin(this.config));
    this.middlewareStack.use(getRetryPlugin(this.config));
    this.middlewareStack.use(getContentLengthPlugin(this.config));
    this.middlewareStack.use(getHostHeaderPlugin(this.config));
    this.middlewareStack.use(getLoggerPlugin(this.config));
    this.middlewareStack.use(getRecursionDetectionPlugin(this.config));
    this.middlewareStack.use(getHttpAuthSchemeEndpointRuleSetPlugin(this.config, {
      httpAuthSchemeParametersProvider: defaultCognitoIdentityHttpAuthSchemeParametersProvider,
      identityProviderConfigProvider: async (config) => new DefaultIdentityProviderConfig({
        "aws.auth#sigv4": config.credentials
      })
    }));
    this.middlewareStack.use(getHttpSigningPlugin(this.config));
  }
  destroy() {
    super.destroy();
  }
}
class CognitoIdentityServiceException extends ServiceException {
  constructor(options) {
    super(options);
    Object.setPrototypeOf(this, CognitoIdentityServiceException.prototype);
  }
}
class InternalErrorException extends CognitoIdentityServiceException {
  constructor(opts) {
    super({
      name: "InternalErrorException",
      $fault: "server",
      ...opts
    });
    this.name = "InternalErrorException";
    this.$fault = "server";
    Object.setPrototypeOf(this, InternalErrorException.prototype);
  }
}
class InvalidParameterException extends CognitoIdentityServiceException {
  constructor(opts) {
    super({
      name: "InvalidParameterException",
      $fault: "client",
      ...opts
    });
    this.name = "InvalidParameterException";
    this.$fault = "client";
    Object.setPrototypeOf(this, InvalidParameterException.prototype);
  }
}
class LimitExceededException extends CognitoIdentityServiceException {
  constructor(opts) {
    super({
      name: "LimitExceededException",
      $fault: "client",
      ...opts
    });
    this.name = "LimitExceededException";
    this.$fault = "client";
    Object.setPrototypeOf(this, LimitExceededException.prototype);
  }
}
class NotAuthorizedException extends CognitoIdentityServiceException {
  constructor(opts) {
    super({
      name: "NotAuthorizedException",
      $fault: "client",
      ...opts
    });
    this.name = "NotAuthorizedException";
    this.$fault = "client";
    Object.setPrototypeOf(this, NotAuthorizedException.prototype);
  }
}
class ResourceConflictException extends CognitoIdentityServiceException {
  constructor(opts) {
    super({
      name: "ResourceConflictException",
      $fault: "client",
      ...opts
    });
    this.name = "ResourceConflictException";
    this.$fault = "client";
    Object.setPrototypeOf(this, ResourceConflictException.prototype);
  }
}
class TooManyRequestsException extends CognitoIdentityServiceException {
  constructor(opts) {
    super({
      name: "TooManyRequestsException",
      $fault: "client",
      ...opts
    });
    this.name = "TooManyRequestsException";
    this.$fault = "client";
    Object.setPrototypeOf(this, TooManyRequestsException.prototype);
  }
}
class ResourceNotFoundException extends CognitoIdentityServiceException {
  constructor(opts) {
    super({
      name: "ResourceNotFoundException",
      $fault: "client",
      ...opts
    });
    this.name = "ResourceNotFoundException";
    this.$fault = "client";
    Object.setPrototypeOf(this, ResourceNotFoundException.prototype);
  }
}
class ExternalServiceException extends CognitoIdentityServiceException {
  constructor(opts) {
    super({
      name: "ExternalServiceException",
      $fault: "client",
      ...opts
    });
    this.name = "ExternalServiceException";
    this.$fault = "client";
    Object.setPrototypeOf(this, ExternalServiceException.prototype);
  }
}
class InvalidIdentityPoolConfigurationException extends CognitoIdentityServiceException {
  constructor(opts) {
    super({
      name: "InvalidIdentityPoolConfigurationException",
      $fault: "client",
      ...opts
    });
    this.name = "InvalidIdentityPoolConfigurationException";
    this.$fault = "client";
    Object.setPrototypeOf(this, InvalidIdentityPoolConfigurationException.prototype);
  }
}
class DeveloperUserAlreadyRegisteredException extends CognitoIdentityServiceException {
  constructor(opts) {
    super({
      name: "DeveloperUserAlreadyRegisteredException",
      $fault: "client",
      ...opts
    });
    this.name = "DeveloperUserAlreadyRegisteredException";
    this.$fault = "client";
    Object.setPrototypeOf(this, DeveloperUserAlreadyRegisteredException.prototype);
  }
}
class ConcurrentModificationException extends CognitoIdentityServiceException {
  constructor(opts) {
    super({
      name: "ConcurrentModificationException",
      $fault: "client",
      ...opts
    });
    this.name = "ConcurrentModificationException";
    this.$fault = "client";
    Object.setPrototypeOf(this, ConcurrentModificationException.prototype);
  }
}
const se_GetCredentialsForIdentityCommand = async (input, context) => {
  const headers = sharedHeaders("GetCredentialsForIdentity");
  let body;
  body = JSON.stringify(_json(input));
  return buildHttpRpcRequest(context, headers, "/", void 0, body);
};
const se_GetIdCommand = async (input, context) => {
  const headers = sharedHeaders("GetId");
  let body;
  body = JSON.stringify(_json(input));
  return buildHttpRpcRequest(context, headers, "/", void 0, body);
};
const de_GetCredentialsForIdentityCommand = async (output, context) => {
  if (output.statusCode >= 300) {
    return de_CommandError(output, context);
  }
  const data = await parseJsonBody(output.body, context);
  let contents = {};
  contents = de_GetCredentialsForIdentityResponse(data);
  const response = {
    $metadata: deserializeMetadata(output),
    ...contents
  };
  return response;
};
const de_GetIdCommand = async (output, context) => {
  if (output.statusCode >= 300) {
    return de_CommandError(output, context);
  }
  const data = await parseJsonBody(output.body, context);
  let contents = {};
  contents = _json(data);
  const response = {
    $metadata: deserializeMetadata(output),
    ...contents
  };
  return response;
};
const de_CommandError = async (output, context) => {
  const parsedOutput = {
    ...output,
    body: await parseJsonErrorBody(output.body, context)
  };
  const errorCode = loadRestJsonErrorCode(output, parsedOutput.body);
  switch (errorCode) {
    case "InternalErrorException":
    case "com.amazonaws.cognitoidentity#InternalErrorException":
      throw await de_InternalErrorExceptionRes(parsedOutput);
    case "InvalidParameterException":
    case "com.amazonaws.cognitoidentity#InvalidParameterException":
      throw await de_InvalidParameterExceptionRes(parsedOutput);
    case "LimitExceededException":
    case "com.amazonaws.cognitoidentity#LimitExceededException":
      throw await de_LimitExceededExceptionRes(parsedOutput);
    case "NotAuthorizedException":
    case "com.amazonaws.cognitoidentity#NotAuthorizedException":
      throw await de_NotAuthorizedExceptionRes(parsedOutput);
    case "ResourceConflictException":
    case "com.amazonaws.cognitoidentity#ResourceConflictException":
      throw await de_ResourceConflictExceptionRes(parsedOutput);
    case "TooManyRequestsException":
    case "com.amazonaws.cognitoidentity#TooManyRequestsException":
      throw await de_TooManyRequestsExceptionRes(parsedOutput);
    case "ResourceNotFoundException":
    case "com.amazonaws.cognitoidentity#ResourceNotFoundException":
      throw await de_ResourceNotFoundExceptionRes(parsedOutput);
    case "ExternalServiceException":
    case "com.amazonaws.cognitoidentity#ExternalServiceException":
      throw await de_ExternalServiceExceptionRes(parsedOutput);
    case "InvalidIdentityPoolConfigurationException":
    case "com.amazonaws.cognitoidentity#InvalidIdentityPoolConfigurationException":
      throw await de_InvalidIdentityPoolConfigurationExceptionRes(parsedOutput);
    case "DeveloperUserAlreadyRegisteredException":
    case "com.amazonaws.cognitoidentity#DeveloperUserAlreadyRegisteredException":
      throw await de_DeveloperUserAlreadyRegisteredExceptionRes(parsedOutput);
    case "ConcurrentModificationException":
    case "com.amazonaws.cognitoidentity#ConcurrentModificationException":
      throw await de_ConcurrentModificationExceptionRes(parsedOutput);
    default:
      const parsedBody = parsedOutput.body;
      return throwDefaultError({
        output,
        parsedBody,
        errorCode
      });
  }
};
const de_ConcurrentModificationExceptionRes = async (parsedOutput, context) => {
  const body = parsedOutput.body;
  const deserialized = _json(body);
  const exception = new ConcurrentModificationException({
    $metadata: deserializeMetadata(parsedOutput),
    ...deserialized
  });
  return decorateServiceException(exception, body);
};
const de_DeveloperUserAlreadyRegisteredExceptionRes = async (parsedOutput, context) => {
  const body = parsedOutput.body;
  const deserialized = _json(body);
  const exception = new DeveloperUserAlreadyRegisteredException({
    $metadata: deserializeMetadata(parsedOutput),
    ...deserialized
  });
  return decorateServiceException(exception, body);
};
const de_ExternalServiceExceptionRes = async (parsedOutput, context) => {
  const body = parsedOutput.body;
  const deserialized = _json(body);
  const exception = new ExternalServiceException({
    $metadata: deserializeMetadata(parsedOutput),
    ...deserialized
  });
  return decorateServiceException(exception, body);
};
const de_InternalErrorExceptionRes = async (parsedOutput, context) => {
  const body = parsedOutput.body;
  const deserialized = _json(body);
  const exception = new InternalErrorException({
    $metadata: deserializeMetadata(parsedOutput),
    ...deserialized
  });
  return decorateServiceException(exception, body);
};
const de_InvalidIdentityPoolConfigurationExceptionRes = async (parsedOutput, context) => {
  const body = parsedOutput.body;
  const deserialized = _json(body);
  const exception = new InvalidIdentityPoolConfigurationException({
    $metadata: deserializeMetadata(parsedOutput),
    ...deserialized
  });
  return decorateServiceException(exception, body);
};
const de_InvalidParameterExceptionRes = async (parsedOutput, context) => {
  const body = parsedOutput.body;
  const deserialized = _json(body);
  const exception = new InvalidParameterException({
    $metadata: deserializeMetadata(parsedOutput),
    ...deserialized
  });
  return decorateServiceException(exception, body);
};
const de_LimitExceededExceptionRes = async (parsedOutput, context) => {
  const body = parsedOutput.body;
  const deserialized = _json(body);
  const exception = new LimitExceededException({
    $metadata: deserializeMetadata(parsedOutput),
    ...deserialized
  });
  return decorateServiceException(exception, body);
};
const de_NotAuthorizedExceptionRes = async (parsedOutput, context) => {
  const body = parsedOutput.body;
  const deserialized = _json(body);
  const exception = new NotAuthorizedException({
    $metadata: deserializeMetadata(parsedOutput),
    ...deserialized
  });
  return decorateServiceException(exception, body);
};
const de_ResourceConflictExceptionRes = async (parsedOutput, context) => {
  const body = parsedOutput.body;
  const deserialized = _json(body);
  const exception = new ResourceConflictException({
    $metadata: deserializeMetadata(parsedOutput),
    ...deserialized
  });
  return decorateServiceException(exception, body);
};
const de_ResourceNotFoundExceptionRes = async (parsedOutput, context) => {
  const body = parsedOutput.body;
  const deserialized = _json(body);
  const exception = new ResourceNotFoundException({
    $metadata: deserializeMetadata(parsedOutput),
    ...deserialized
  });
  return decorateServiceException(exception, body);
};
const de_TooManyRequestsExceptionRes = async (parsedOutput, context) => {
  const body = parsedOutput.body;
  const deserialized = _json(body);
  const exception = new TooManyRequestsException({
    $metadata: deserializeMetadata(parsedOutput),
    ...deserialized
  });
  return decorateServiceException(exception, body);
};
const de_Credentials = (output, context) => {
  return take(output, {
    AccessKeyId: expectString,
    Expiration: (_) => expectNonNull(parseEpochTimestamp(expectNumber(_))),
    SecretKey: expectString,
    SessionToken: expectString
  });
};
const de_GetCredentialsForIdentityResponse = (output, context) => {
  return take(output, {
    Credentials: (_) => de_Credentials(_),
    IdentityId: expectString
  });
};
const deserializeMetadata = (output) => {
  var _a, _b;
  return {
    httpStatusCode: output.statusCode,
    requestId: (_b = (_a = output.headers["x-amzn-requestid"]) != null ? _a : output.headers["x-amzn-request-id"]) != null ? _b : output.headers["x-amz-request-id"],
    extendedRequestId: output.headers["x-amz-id-2"],
    cfId: output.headers["x-amz-cf-id"]
  };
};
const throwDefaultError = withBaseException(CognitoIdentityServiceException);
const buildHttpRpcRequest = async (context, headers, path, resolvedHostname, body) => {
  const { hostname, protocol = "https", port, path: basePath } = await context.endpoint();
  const contents = {
    protocol,
    hostname,
    port,
    method: "POST",
    path: basePath.endsWith("/") ? basePath.slice(0, -1) + path : basePath + path,
    headers
  };
  if (body !== void 0) {
    contents.body = body;
  }
  return new HttpRequest(contents);
};
function sharedHeaders(operation) {
  return {
    "content-type": "application/x-amz-json-1.1",
    "x-amz-target": "AWSCognitoIdentityService.".concat(operation)
  };
}
class GetCredentialsForIdentityCommand extends Command.classBuilder().ep({
  ...commonParams
}).m(function(Command2, cs, config, o2) {
  return [
    getSerdePlugin(config, this.serialize, this.deserialize),
    getEndpointPlugin(config, Command2.getEndpointParameterInstructions())
  ];
}).s("AWSCognitoIdentityService", "GetCredentialsForIdentity", {}).n("CognitoIdentityClient", "GetCredentialsForIdentityCommand").f(void 0, void 0).ser(se_GetCredentialsForIdentityCommand).de(de_GetCredentialsForIdentityCommand).build() {
}
class GetIdCommand extends Command.classBuilder().ep({
  ...commonParams
}).m(function(Command2, cs, config, o2) {
  return [
    getSerdePlugin(config, this.serialize, this.deserialize),
    getEndpointPlugin(config, Command2.getEndpointParameterInstructions())
  ];
}).s("AWSCognitoIdentityService", "GetId", {}).n("CognitoIdentityClient", "GetIdCommand").f(void 0, void 0).ser(se_GetIdCommand).de(de_GetIdCommand).build() {
}
export {
  CognitoIdentityClient,
  GetCredentialsForIdentityCommand,
  GetIdCommand
};
