package structure;

import java.util.HashSet;
import java.util.Set;

/**
 * Contains all courses offered for a given subject + semester.
 */
public class SemesterAndSubjectCourses {
	/** The semester of classes */
	Semester semester = null;
	/** The subject of classes */
	Subject subject = null;
	/** The classes matching the semester + subject */
	Set<Clazz> classes = new HashSet<>();

	public SemesterAndSubjectCourses(Semester semester, Subject subject) {
		this.semester = semester;
		this.subject = subject;
	}

	/**
	 * @param clazz
	 *            The {@link Clazz} to remeber for this subject + semester
	 *            instance.
	 */
	public void addClass(Clazz clazz) {
		classes.add(clazz);
	}
}
