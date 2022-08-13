var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });

// src/Util/Others/HelpersUtil.ts
function str_replace($search, $replace, $subject) {
  let i, regex = [], map = {};
  for (i = 0; i < $search.length; i++) {
    regex.push($search[i].replace(/([-[\]{}()*+?.\\^$|#,])/g, "\\$1"));
    map[$search[i]] = $replace[i];
  }
  regex = regex.join("|");
  $subject = $subject.replace(new RegExp(regex, "g"), function(matched) {
    return map[matched];
  });
  return $subject;
}
__name(str_replace, "str_replace");
function slug($string, $separator = "-") {
  $string = $string.trim();
  $string = str_replace(["&", "@", "%", "$", "*", "<", ">", "+", "!"], [
    " and",
    " at",
    " percentage",
    " dollar",
    " asterisk ",
    " less than",
    " greater than",
    " plus",
    "exclamation"
  ], $string);
  return $string.toLocaleString().toLowerCase().normalize("NFD").replace(/[^\S]+/g, $separator);
}
__name(slug, "slug");
function isValidTagName(tagName) {
  return document.createElement(tagName).toString() !== "[object HTMLUnknownElement]";
}
__name(isValidTagName, "isValidTagName");

// src/Util/Others/TableOfContent.ts
var TableOfContent = class {
  constructor($tableOfContentContainer = "") {
    this._$tableOfContentDetails = {};
    this._tocTree = [];
    this._breakLoopBackward = false;
    this._tocResult = "";
    this._$tableOfContentDetails = {
      tocContainer: $tableOfContentContainer,
      noOfHeadersFound: 0,
      tocDepth: 4,
      tocNoHeadingToTrigger: 2,
      tocLabel: "Table Of Content",
      tocLabelTag: "h2",
      tocClass: "tonics-toc"
    };
  }
  get breakLoopBackward() {
    return this._breakLoopBackward;
  }
  set breakLoopBackward(value) {
    this._breakLoopBackward = value;
  }
  get tocResult() {
    return this._tocResult;
  }
  set tocResult(value) {
    this._tocResult = value;
  }
  get tocTree() {
    return this._tocTree;
  }
  set tocTree(value) {
    this._tocTree = value;
  }
  get $tableOfContentDetails() {
    return this._$tableOfContentDetails;
  }
  set $tableOfContentDetails(value) {
    this._$tableOfContentDetails = value;
  }
  settings() {
    this._$tableOfContentDetails = {
      tocDepth: 4,
      tocNoHeadingToTrigger: 2,
      tocLabel: "Table Of Content",
      tocLabelTag: "h2",
      tocClass: ".tonics-toc"
    };
  }
  tocContainer(tag) {
    this._$tableOfContentDetails.tocContainer = tag;
    return this;
  }
  tocDepth(int) {
    this._$tableOfContentDetails.tocDepth = int;
    return this;
  }
  tocNoHeadingToTrigger(int) {
    this._$tableOfContentDetails.tocNoHeadingToTrigger = int;
    return this;
  }
  tocLabel(label) {
    this._$tableOfContentDetails.tocLabel = label;
    return this;
  }
  tocLabelTag(labelTag) {
    this._$tableOfContentDetails.tocLabelTag = labelTag;
    return this;
  }
  tocClass(tocClass) {
    this._$tableOfContentDetails.tocClass = tocClass;
    return this;
  }
  run() {
    const toc = this._$tableOfContentDetails;
    if (document.querySelector(toc.tocContainer)) {
      let selector = "h1, h2, h3, h4";
      if (toc.tocDepth <= 6) {
        selector = "";
        for (let i = 1; i <= toc.tocDepth; i++) {
          if (i === toc.tocDepth) {
            selector += `h${i}`;
          } else {
            selector += `h${i}, `;
          }
        }
      }
      let tocHeader = document.querySelector(toc.tocContainer).querySelectorAll(selector);
      if (tocHeader.length < toc.tocNoHeadingToTrigger) {
        return;
      }
      const isTagNameValid = isValidTagName(toc.tocLabelTag);
      if (!isTagNameValid) {
        throw new DOMException(toc.tocLabelTag + " is not a valid tagName ");
      }
      if (tocHeader.length > 0) {
        toc.noOfHeadersFound = tocHeader.length;
        let currentLevel = 0, lastAddedElementToTree = 0, result = `<ul>`, item = {}, childStack = [];
        tocHeader.forEach((header, index) => {
          if (header.textContent.length > 0) {
            let headerIDSlug = slug(header.textContent);
            let headerText = header.textContent;
            if (!header.hasAttribute("id")) {
              header.id = headerIDSlug;
            } else if (header.id.trim() === "") {
              header.id = headerIDSlug;
            }
            currentLevel = parseInt(header.tagName[1]);
            item = {
              "level": currentLevel,
              "headerID": headerIDSlug,
              "headerText": headerText
            };
            item.data = `<li data-heading_level="${currentLevel}"><a href="#${headerIDSlug}"><span class="toctext">${headerText}</span></a>`;
            if (this.tocTree.length === 0) {
              this.pushToTree(item);
            } else {
              if (this.tocTree[lastAddedElementToTree].level === currentLevel) {
                this.tocTree[lastAddedElementToTree].data += "</li>" + item.data;
              }
              if (currentLevel > this.tocTree[lastAddedElementToTree].level) {
                this.tocTree[lastAddedElementToTree].data += "<ul>";
                this.pushToTree(item);
                lastAddedElementToTree = this.tocTree.length - 1;
                childStack.push(item);
              }
              if (currentLevel < this.tocTree[lastAddedElementToTree].level) {
                let timesToClose = 0;
                for (const treeData of this.loopTreeBackward(childStack)) {
                  if (treeData.level > currentLevel) {
                    treeData.data += "</li>";
                    childStack.pop();
                    timesToClose++;
                  }
                  if (treeData.level === currentLevel) {
                    this.breakLoopBackward = true;
                  }
                }
                item.data = "</ul></li>".repeat(timesToClose) + item.data;
                this.pushToTree(item);
                lastAddedElementToTree = this.tocTree.length - 1;
              }
              if (index === tocHeader.length - 1) {
                this.tocTree[lastAddedElementToTree].data += "</li>";
              }
            }
          }
        });
        this.tocTree.forEach((item2) => {
          result += item2.data;
        });
        result += "</ul>";
        result = `<div class="${toc.tocClass}"> <${toc.tocLabelTag}> ${toc.tocLabel} </${toc.tocLabelTag}> ${result} </div>`;
        this._tocResult = result;
      }
    }
  }
  pushToTree(item) {
    item.index = this.tocTree.length;
    this.tocTree.push(item);
  }
  *loopTreeBackward(treeToLoop = null) {
    if (treeToLoop === null) {
      treeToLoop = this.tocTree;
    }
    for (let i = treeToLoop.length - 1; i >= 0; i--) {
      if (this.breakLoopBackward) {
        break;
      }
      yield treeToLoop[i];
    }
    this.breakLoopBackward = false;
  }
};
__name(TableOfContent, "TableOfContent");
if (!window.hasOwnProperty("TonicsScript")) {
  window.TonicsScript = {};
}
window.TonicsScript.TableOfContent = ($tableOfContentContainer) => new TableOfContent($tableOfContentContainer);
export {
  TableOfContent
};

class OnBeforeTonicsFieldPreviewEventHandlerForTonicsToc {
    constructor(event) {
        const ElTarget = event.getElementTarget()?.closest('.tabs');
        handleTonicsToc(event, ElTarget);
    }
}

class OnBeforeTonicsFieldSubmitEventHandlerForTonicsToc {
    constructor(event) {
        const ElTarget = event.getElementTarget()?.closest('.tabs');
        handleTonicsToc(event, ElTarget);
    }
}

if (window?.parent.TonicsEvent?.EventConfig){
    window.parent.TonicsEvent.EventConfig.OnBeforeTonicsFieldPreviewEvent.push(OnBeforeTonicsFieldPreviewEventHandlerForTonicsToc);
    window.parent.TonicsEvent.EventConfig.OnBeforeTonicsFieldSubmitEvent.push(OnBeforeTonicsFieldSubmitEventHandlerForTonicsToc);
}

function handleTonicsToc(event, target) {
    if (target.closest('.tabs').querySelector("input[value='app-tonicstoc']")) {
        const TocEditor = window.TonicsScript.TableOfContent('.entry-content');
        TocEditor.tocDepth(6).run();
        const headersFound = TocEditor.$tableOfContentDetails.noOfHeadersFound;
        const Tree = TocEditor.tocTree
        event._postData.tableOfContentData = {
            'headersFound': headersFound,
            'tree': Tree
        };
    }
}