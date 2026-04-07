(defpackage :singularity-daemon
  (:use :cl)
  (:export :main))

(in-package :singularity-daemon)

(defvar *running* t)

(defun log-msg (msg)
  (format t "~A ~A~%" (get-universal-time) msg)
  (finish-output))

(defun handle-shutdown ()
  (setf *running* nil)
  (log-msg "Shutdown requested"))

(defun main-loop ()
  (log-msg "Daemon started")
  (loop while *running* do
    (log-msg "Heartbeat")
    (sleep 5))
  (log-msg "Daemon stopped"))

(defun main ()
  (main-loop))




**Error due to lack of handling of the SIGTERM signal from systemd.
First error – missing SIGTERM invocation:**
```
(defun handle-shutdown ()
  (setf *running* nil)
  (log-msg "Shutdown requested"))
```
**solution:**
```
(defun handle-signal (signal)
  (declare (ignore signal))
  (setf *running* nil)
  (log-msg "Shutdown requested"))
```
**The second error - is the lack of SIGTERM handling:**
```
(defun main-loop ()
  (log-msg "Daemon started")
  (loop while *running* do
    (log-msg "Heartbeat")
    (sleep 5))
  (log-msg "Daemon stopped"))
```
**solution - SIGTERM processing installation:**
```
(defun main-loop ()
  (log-msg "Daemon started")
  (sb-ext:enable-debugger-hook)
  (sb-ext:with-signals ((:TERM (lambda (sig) (handle-signal sig))))
    (loop while *running* do
         (log-msg "Heartbeat")
         (sleep 5))))
  (log-msg "Daemon stopped"))

(defun main ()
  (main-loop))
```
