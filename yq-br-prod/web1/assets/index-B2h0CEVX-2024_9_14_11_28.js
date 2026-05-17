const __vite__fileDeps=["assets/App-BLdT6wOK-2024_9_14_11_28.js","assets/index-CKtHrVPI-2024_9_14_11_28.js","assets/index-DQZyYQwA-2024_9_14_11_28.css","assets/App-CwfC3lo0-2024_9_14_11_28.css"],__vite__mapDeps=i=>i.map(i=>__vite__fileDeps[i]);
import { r as reactExports, a1 as __vitePreload, P as useReactive, J as Cache, N as jsxRuntimeExports, a4 as Loading } from "./index-CKtHrVPI-2024_9_14_11_28.js";
import { q as customFormatTimer } from "./App-BLdT6wOK-2024_9_14_11_28.js";
const svg_theme_fill_color = "_svg_theme_fill_color_144s5_3";
const contentBox = "_contentBox_144s5_55";
const title = "_title_144s5_154";
const message_title = "_message_title_144s5_238";
const content = "_content_144s5_55";
const time = "_time_144s5_356";
const css = {
  svg_theme_fill_color,
  contentBox,
  title,
  message_title,
  content,
  time
};
const InnerPageWithBack = reactExports.lazy(() => __vitePreload(() => import("./App-BLdT6wOK-2024_9_14_11_28.js").then((n) => n.a8), true ? __vite__mapDeps([0,1,2,3]) : void 0));
const MessageDetail = () => {
  const state = useReactive({
    info: {}
  });
  reactExports.useLayoutEffect(() => {
    state.info = Cache.get("messageDetail") || {};
  }, []);
  const { info } = state;
  return /* @__PURE__ */ jsxRuntimeExports.jsx(reactExports.Suspense, { fallback: /* @__PURE__ */ jsxRuntimeExports.jsx(Loading, {}), children: /* @__PURE__ */ jsxRuntimeExports.jsx(InnerPageWithBack, { title: info.title, children: /* @__PURE__ */ jsxRuntimeExports.jsxs("div", { className: css.contentBox, children: [
    /* @__PURE__ */ jsxRuntimeExports.jsx(
      "div",
      {
        className: "".concat(css.title, " ").concat(state.info.page_type === "message" && css.message_title),
        children: info.title
      }
    ),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: css.time, children: info.created_at && customFormatTimer(info.created_at) || info.send_at && customFormatTimer(info.send_at) }),
    /* @__PURE__ */ jsxRuntimeExports.jsx("div", { className: css.content, children: info.content })
  ] }) }) });
};
export {
  MessageDetail as default
};
