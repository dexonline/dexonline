/**
 * @file Simulates typing into an HTML element.
 * @author Mark E. Haase <mehaase@gmail.com>
 * @version 1.0.0
 */

/**
 * A class for simulating a person typing into an HTML element.
 *
 * @class Typewriter
 * @param {HTMLElement} element An HTML element to type the text inside of.
 */
function Typewriter (element) {
    // Initialize callbacks.
    this._characterCallback = null;
    this._completionCallback = null;

    // Convert jQuery object to plain DOM object.
    if (typeof jQuery != 'undefined' && element instanceof jQuery) {
        element = element[0];
    }

    // Create a text node if this element doesn't already have one.
    this._textNode = null;
    this._isTextNode = element.nodeType === Node.TEXT_NODE;

    if (this._isTextNode) {
      this._textNode = element;
    }
    else {
      for (var i = 0; i < element.childNodes.length; i++) {
          if (element.childNodes[i].nodeType == 3) {
              _textNode = element.childNodes[i];
              break;
          }
      }

      if (!this._textNode) {
          this._textNode = document.createTextNode("");
          element.appendChild(this._textNode);
      }

      // Create the caret.
      this._caretElement = document.createElement("span");
      this._caretTextNode = document.createTextNode("");
      this._caretElement.appendChild(this._caretTextNode);
      element.appendChild(this._caretElement);

      this.setCaret("|");
      this.setCaretPeriod(1000);
    }

    // Initialize the delay distribution.
    this.setDelay(250, 100);
}

/**
 * Play a sequence of typing animations.
 *
 * @param  {Array} sequence A sequence of animations to play.
 *
 * The {sequence} is an array of objects like this:
 *
 * [
 *     {
 *         text: "trinity@localhost:~$ ",
 *         instant: true,
 *         delayAfter: 500,
 *         callback: function() {console.log("Done!")}
 *     },
 *     ...
 * ]
 *
 * The {text} field is the text that will be displayed. The optional {instant}
 * boolean controls whether the text will be displayed instantly
 * (e.g. ignoring the current delay). The {delayAfter} integer controls
 * how long to wait (in milliseconds) before animating the next item
 * in the sequence. The {callback} function will be called as soon as the
 * text is finished displaying.
 */
Typewriter.prototype.playSequence = function (sequence) {
    this._playSequenceAtIndex(sequence, 0);
}

/**
 * Set the caret character.
 *
 * @param {string} character A character to use as the caret.
 *
 * Pass a blank string to effectively hide the caret.
 */
Typewriter.prototype.setCaret = function (character) {
    this._caretTextNode.nodeValue = character;
}

/**
 * Change caret's flashing speed.
 *
 * @param {int} [caretPeriod] The period of the flashing caret in milliseconds.
 *
 * Pass zero to disable flashing.
 */
Typewriter.prototype.setCaretPeriod = function (period) {
    var that = this;

    if (this._caretInterval) {
        clearInterval(this._caretInterval);
    }

    if (period) {
        this._caretInterval = setInterval(function () {
            if (that._caretElement.style.display == "none") {
                that._caretElement.style.display = "";
            } else {
                that._caretElement.style.display = "none";
            }
        }, period);
    } else {
        that._caretElement.style.display = "";
    }
}

/**
 * Set the function that is called after each character is typed.
 *
 * @param {Function} callback Called after each character is typed.
 */
Typewriter.prototype.setCharacterCallback = function (callback) {
    this._characterCallback = callback;
}

/**
 * Set the function that is called after all characters have been typed.
 *
 * @param {Function} callback Called after all characters have been typed.
 */
Typewriter.prototype.setCompletionCallback = function (callback) {
    this._completionCallback = callback;
}

/**
 * Change the randomized delay between keystrokes.
 *
 * @param {int} mean The average length of time in milliseconds between keystrokes.
 * @param {int} variance The maximum variance in milliseconds (away from the mean) between keystrokes.
 *
 * For example, with mean = 200 and variance = 50, each delay will
 * be sampled from the uniform distribution over [150, 250).
 */
Typewriter.prototype.setDelay = function (mean, variance) {
    this._delayMean = mean;
    this._delayVariance = variance;
}

/**
 * Simulate somebody typing text into an element.
 *
 * @param {string} text The text to be typed.
 * @param {boolean} [instant=false] If true, ignore the currently configured delay and don't use character callback.
 */
Typewriter.prototype.typeText = function (text, instant) {
    if (typeof instant === 'undefined') {
        instant = false;
    }

    if (instant || this._delayMean == 0) {
        this._textNode.nodeValue += text;

        if (this._completionCallback) {
            this._completionCallback();
        }
    } else {
        this._typeTextAtIndex(text, 0);
    }
}

/**
 * Play a single animation from a sequence.
 *
 * @param  {Array} sequence A sequence of animations.
 * @param  {int} index The index of the animation to play.
 */
Typewriter.prototype._playSequenceAtIndex = function (sequence, index) {
    var that = this;

    // Check if we're at the end of the sequence.
    if (index == sequence.length) {
        return;
    }

    var currentItem = sequence[index];

    // Schedule the next item.
    var delayAfter = currentItem.delayAfter || 0;

    this.setCompletionCallback(function () {
        if (currentItem.callback) {
            currentItem.callback();
        }

        setTimeout(function () {that._playSequenceAtIndex(sequence, index + 1)}, delayAfter);
    });

    // Play the current item.
    this.typeText(currentItem.text, currentItem.instant);
}

/**
 * Return an integer value sampled from the delay distribution.
 *
 * @return {int} Sampled from delay mean and variance.
 */
Typewriter.prototype._sampleDelay = function () {
    var lower_bound = this._delayMean - this._delayVariance;
    var range = this._delayVariance * 2;

    return Math.floor(Math.random() * range + lower_bound);
}

/**
 * A helper that types one character at a time.
 *
 * @param {string} text - The text to be typed.
 * @param {string} text - The text to be typed.
 * @param {Function} [characterCallback] Called after every character has been typed.
 * @param {Function} [completionCallback] Called after _all_ characters have been typed.
 */
Typewriter.prototype._typeTextAtIndex = function (text, index) {
    var that = this;

    // Are we at the end of the text?
    if (index == text.length) {
        if (this._completionCallback) {
            this._completionCallback();
        }
        return;
    }

    // Type the character.
    var character = text.charAt(index);

    if (character === "\b") {
        // This is a delete character: _remove_ the last character.
        var newLength = this._textNode.nodeValue.length - 1;
        this._textNode.nodeValue = this._textNode.nodeValue.substring(0, newLength);
    } else {
        this._textNode.nodeValue += text[index];
    }

    if (this._characterCallback) {
        this._characterCallback(character);
    }

    // Schedule the next character.
    setTimeout(
        function () {
            that._typeTextAtIndex(text, index + 1);
        },
        this._sampleDelay()
    );
}
